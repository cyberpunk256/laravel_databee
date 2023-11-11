import 'vue-toastification/dist/index.css'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import { useToast } from "vue-toastification"
const toast = useToast()

export default {
    data: () => ({
        dataForm: useForm({})
    }),
    computed: {
        user() {
            return this.$page.props.auth.user
        },
        enums() {
            return this.$page.props.enums
        },
        flash() {
            return this.$page.props.flash
        },
        bucket_path() {
            return this.$page.props.bucket_path
        }
    },
    methods: {
        show_toast() {
            console.log('flash', this.flash);
            if (this.flash.success) {
                toast.success(this.flash.success, { timeout: 1000 });
            } else if (this.flash.error) {
                toast.error(this.flash.error, { timeout: 1000 });
            } else {
                toast.error("エラーが発生しました。", { timeout: 1000 });
            }
        },
        get_path_url(path) {
            console.log('path', this.bucket_path  + path)
            return this.bucket_path + path
        },
    }
};