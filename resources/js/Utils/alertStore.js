import { reactive } from 'vue';

export const alertStore = reactive({
    message: '',
    type: 'success', // success, error, info, warning
    visible: false,

    show(message, type = 'success', duration = 3000) {
        this.message = message;
        this.type = type;
        this.visible = true;

        setTimeout(() => {
            this.visible = false;
        }, duration);
    }
});
