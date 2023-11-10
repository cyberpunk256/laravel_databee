import 'vue-toastification/dist/index.css'
import { useToast } from "vue-toastification"
const toast = useToast()

export default {
    data: () => ({}),
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
        get_file(path) {
            return `/api/get_file?path=${path}`
        },
    }
};