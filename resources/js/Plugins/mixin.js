import 'vue-toastification/dist/index.css'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import { useToast } from "vue-toastification"
const toast = useToast()

export default {
    data: () => ({
    }),
    computed: {
        user() {
            return this.$page.props.auth.user
        },
        flash() {
            return this.$page.props.flash
        },
        constant() {
            return this.$page.props.constant
        },
    },
    methods: {
        show_toast(status = null, message= null) {
            if (this.flash.success) {
                toast.success(this.flash.success, { timeout: 1000 });
            } else if (this.flash.error) {
                toast.error(this.flash.error, { timeout: 1000 });
            } else {
                if(status) {
                    if(status == 'success') {
                        toast.success(message, { timeout: 1000 });
                    } else {
                        toast.error(message, { timeout: 1000 });
                    }
                } else {
                    toast.error("エラーが発生しました。", { timeout: 1000 });
                }
            }
        },
        async get_path_url(path) {
            try {
                const { data } = await axios.post(`/api/media/presigned_url`, { path: path });
                return data.presigned_url
            } catch(e) {
                console.log(e);
                return ""
            }
        },
        getTextOfOption(options, value) {
            const option = options.find(x => x.value == value)
            return option ? option.text : null;
        },
        getYmdHiFromDTS(datetime_str, apply_limit = 0) {
            return moment(datetime_str, "YYYY-MM-DD HH:mm:ss")
                .add(apply_limit, "days")
                .format("YYYY年MM月DD日 HH:mm")
        },
    }
};