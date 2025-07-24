import {usePage} from "@inertiajs/vue3";
import {nextTick} from "vue";
import axios from "axios";

export const inertiaStaticPropsPlugin = {
    install() {
        const staticProps = {};

        nextTick(() => {
            const page = usePage();

            if (page.props.staticProps) {
                loadStaticProps(page.props.staticProps, page.props);
            }
        })

        axios.interceptors.response.use(response => {
            if (shouldInjectStaticProps(response)) {
                injectStaticProps(response);
            }

            return response;
        });

        function loadStaticProps(keys, props) {
            for (const key of keys) {
                staticProps[key] = props[key];
            }
        }

        function shouldInjectStaticProps(response) {
            return response.headers['x-inertia'] && !response.config.headers['X-Inertia-Partial-Data'];
        }

        function injectStaticProps(response) {
            const data = typeof response.data === 'object' ? response.data : JSON.parse(response.data)

            if (data.props.staticProps) {
                loadStaticProps(data.props.staticProps, data.props)
            }

            for (const key in staticProps) {
                if (data.props[key]) {
                    continue
                }

                data.props[key] = staticProps[key]
            }

            if (typeof response.data === 'object') {
                response.data = data
            } else {
                response.data = JSON.stringify(data)
            }
        }
    }
}

export default inertiaStaticPropsPlugin;
