import { useToast as useToastOg } from "vue-toast-notification";

const toast = useToastOg();
let instance;

export const useToast = () => {
    const showToast = (
        message: string,
        type: "success" | "error" = "success"
    ) => {
        if (instance) instance.dismiss();

        setTimeout(() => {
            instance = toast.open({
                type: "info",
                message: message,
                position: "bottom",
                duration: type === "success" ? 1000 : 5000,
            });
        }, 100);
    };

    return {
        success: (message: string) => showToast(message, "success"),
        error: (message: string) => showToast(message, "error"),
    };
};
