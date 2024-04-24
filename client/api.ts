import axios from "axios";

export const api = axios.create({
    baseURL: "/api",
    headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
    },
});

api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response) {
            alert(
                error.response?.data.message || error.response?.data || error
            );
        }
        return Promise.reject(error);
    }
);
