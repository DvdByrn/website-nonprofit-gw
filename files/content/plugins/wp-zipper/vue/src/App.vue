<template>
  <div id="zipper">

    <div v-if="current_action && 'init' == screen">
      {{ current_action }}
    </div>

    <div v-if="errors.length > 0">
      <ul class="errors">
        <li v-for="error in errors">
          {{ error }}
        </li>
      </ul>
    </div>

    <div v-if="file_index">
      <h3>File Index:</h3>
      <p>Total Files: {{ file_index.total_files }}</p>
      <p>Total Directories: {{ file_index.total_dir }}</p>
      <p>File Size: {{ file_index.size }}</p>
      <p>Ram used to build index: {{ file_index.ram_used }}</p>
    </div>

    <div v-if="database_index">
      <h3>Database:</h3>
      <p>Total tables: {{ database_index.length }}</p>
    </div>

    <p v-if="state.indexing_files">
      Please wait while files are being indexed. This can take a while.
    </p>

    <p v-if="state.indexing_database">
      Please wait while the database is being indexed.
    </p>

    <div v-if="screen == 'init'">
      <p>The backup process can take several minutes.</p>
      <button class="button button-primary" @click="get_file_index" :disabled="busy">
        <i class="fa fa-spin fa-spinner" v-if="busy"></i>
        <i class="fa fa-plus" v-else></i>
        {{ busy ? current_action : 'Start New Backup' }}
      </button>
    </div>

    <div v-if="screen == 'config'">

      <hr>

      <h3>Backup Configuration:</h3>

      <p>
        <label>
          <input type="checkbox" v-model="archive_options.all_files"> Include all files -
          <i>{{ file_index ? file_index.files.length : 0 }} files</i>
        </label>
      </p>

      <div v-if="! archive_options.all_files">
        <select multiple="true" v-model="archive_options.files">
          <option v-for="file in file_index.files" :value="file.path">
            {{ file.path }} - {{ file.size }}
          </option>
        </select>
      </div>

      <p>
        <label>
          <input type="checkbox" v-model="archive_options.all_tables"> Include all database tables -
          <i>{{ database_index ? database_index.length : 0 }} tables</i>
        </label>
      </p>

      <div v-if="! archive_options.all_tables">
        <select multiple="true" v-model="archive_options.database_tables">
          <option v-for="table in database_index" :value="table">
            {{ table }}
          </option>
        </select>
      </div>

      <hr>

      <div class="s8">
        <p>{{ current_action ? current_action : '' }}</p>
        <div class="progress">
          <div class="progress-bar progress-bar-info" role="progressbar" :style="{ width: progress + '%' }">
          </div>
        </div>
        <p>Remaining items: {{ remaining_items }}</p>
      </div>

      <p>
        <button class="button button-primary" @click="run_zipper" :disabled="busy">
          <i class="fa fa-spin fa-spinner" v-if="busy"></i>
          <i class="fa fa-floppy-o" v-else></i>
          {{ busy ? current_action : 'Build Zip Archive' }}
        </button>
      </p>

    </div>

  </div>
</template>

<script>
    import Helpers from './helpers'

    export default {
        name: 'app',
        data () {
            return {
                done: false,
                screen: 'init',
                busy: false,
                current_action: false,
                database_index: false,
                file_index: false,
                state: {
                    indexing_files: false,
                    indexing_database: false,
                },
                errors: [],
                archive_options: {
                    database_tables: [],
                    files: [],
                    all_files: true,
                    all_tables: true,
                },
                queue: {
                    files: [],
                    tables: [],
                    filename: false,
                    total_files: 0,
                    total_tables: 0,
                }
            }
        },
        computed: {
            remaining_items() {
                return this.queue.files.length;
            },
            progress() {

                if ('Processing files...' === this.current_action) {
                    let total = this.queue.total_files,
                        current = this.queue.files.length;
                    return 100 - ( ( current / total ) * 100 ).toFixed(2);
                }

                if ('Processing database tables...' === this.current_action) {
                    let total = this.queue.total_tables,
                        current = this.queue.tables.length;
                    return 100 - ( ( current / total ) * 100 ).toFixed(2);
                }

                return 0;
            }
        },
        methods: {

            run_zipper() {
                this.clean_up();
            },

            clean_up() {
                this.busy = true;
                this.current_action = 'Cleaning temporary files';

                let url = Helpers.get_url(ajaxurl, {action: 's8_zipper_clean_up'});

                this.$http.get(url).then(r => {
                    this.build_zip_archive();
                }, r => {
                    this.errors.push('Unable to clean temporary files.');
                });
            },

            get_file_index() {
                this.busy = true;
                this.state.indexing_files = true;
                this.current_action = 'Building file index';
                let url = Helpers.get_url(ajaxurl, {action: 's8_zipper_get_file_manifest'});

                this.$http.get(url).then(r => {
                    this.state.indexing_files = false;
                    this.file_index = r.data;
                    this.archive_options.files = r.data.files.map(f => {
                        return f.path;
                    });
                    this.get_database_index();
                }, r => {
                    this.busy = false;
                    this.state.indexing_files = false;
                    this.errors.push('Unable to retrieve file manifest, archive failed.');
                });
            },

            get_database_index() {
                this.busy = true;
                this.state.indexing_database = true;
                this.current_action = 'Building database index';
                let url = Helpers.get_url(ajaxurl, {action: 's8_zipper_get_database_manifest'});

                this.$http.get(url).then(r => {
                    this.state.indexing_database = false;
                    this.database_index = r.data;
                    this.archive_options.database_tables = r.data;
                    this.busy = false;
                    this.screen = 'config';
                }, r => {
                    this.busy = false;
                    this.state.indexing_database = false;
                    this.errors.push('Unable to retrieve database manifest, archive failed.');
                });
            },

            build_zip_archive() {
                this.busy = true;

                _.each( this.archive_options.files, (f) => {
                    this.queue.files.push(f);
                });
                _.each( this.archive_options.database_tables, (t) => {
                    this.queue.tables.push(t);
                });

                this.queue.total_files = this.queue.files.length;
                this.queue.total_tables = this.queue.tables.length;

                let url = Helpers.get_url(ajaxurl, {action: 's8_zipper_get_working_archive'});

                // Get working zip archive.
                this.$http.get(url).then(r => {
                    this.queue.filename = r.data;
                    this.process_queue();
                }, r => {
                    this.busy = false;
                    this.errors.push('Unable to generate zip archive.');
                });
            },

            concatenate_sql() {
                let url = Helpers.get_url(ajaxurl, {action: 's8_zipper_concatenate_sql'}),
                    post_data = {filename: this.queue.filename};
                this.$http.post(url, post_data).then(r => {
                    this.current_action = 'Cleaning up temporary files.';
                    let url = Helpers.get_url(ajaxurl, {action: 's8_zipper_clean_up'});

                    this.$http.get(url).then(r => {
                        this.current_action = 'Backup complete!';
                        alert('Back Up Complete, redirecting to backups.');
                        window.location = _zipper.backups_url;

                    }, r => {
                        this.errors.push('Unable to clean temporary files.');
                    });
                }, r => {
                    this.busy = false;
                    this.errors.push('Unable to concatenate SQL.');
                });
            },

            process_queue() {

                let chunk_size = 100;

                if (chunk_size > this.queue.files.length) {
                    chunk_size = this.queue.files.length;
                }

                // If there are files left, process them.
                if (this.queue.files.length > 0)
                {
                    this.current_action = 'Processing files...';
                    let chunk = this.queue.files.slice(0, chunk_size),
                        url = Helpers.get_url(ajaxurl, {action: 's8_zipper_process_files'}),
                        post_data = {files: chunk, filename: this.queue.filename};

                    // Post files to server.
                    this.$http.post(url, post_data).then(r => {
                        this.queue.filename = r.data;
                        _.each(chunk, (path) => {
                            let i = this.queue.files.indexOf(path);
                            this.queue.files.splice(i, 1);
                        });
                        return this.process_queue();
                    }, r => {
                        this.busy = false;
                        this.errors.push('Unable to generate zip archive.');
                    });
                    return;
                }

                // If there are tables left, process them.
                if (this.queue.tables.length > 0) {
                    this.current_action = 'Processing database tables...';

                    let table = this.queue.tables[0],
                        url = Helpers.get_url(ajaxurl, {action: 's8_zipper_process_table'}),
                        post_data = {table: table, filename: this.queue.filename};

                    // Post files to server.
                    this.$http.post(url, post_data).then(r => {
                        this.queue.tables.splice(0, 1);
                        return this.process_queue();
                    }, r => {
                        this.busy = false;
                        this.errors.push('Unable to generate zip archive.');
                    });
                    return;
                }

                this.concatenate_sql();
            }
        }
    }
</script>

<style lang="scss" scoped>
  #zipper {
    margin-top: 15px;

    .errors {
      color: red;
    }

    select {
      height: 225px;
    }
  }
</style>
