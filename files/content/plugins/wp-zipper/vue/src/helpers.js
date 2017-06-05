export default class Helpers
{
    /**
     * Prepare an encode URL for GET requests.
     * @param url
     * @param params
     * @returns {*}
     */
    static get_url( url, params )
    {
        let output = url;

        if ( -1 === output.indexOf('?') )
        {
            output += '?';

            _.each( params, (v,k) => {
                k = encodeURI(k);
                v = encodeURI(v);
                output += `${k}=${v}&`;
            });

            return output;
        }
    }
}