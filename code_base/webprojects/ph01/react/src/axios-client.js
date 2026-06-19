import axios from "axios";

const axiosClient = axios.create({
    baseURL: `${import.meta.env.VITE_API_BASE_URL}/api`
});

//Interceptors
axiosClient.interceptors.request.use( (config) => {

    const token = localStorage.getItem('ACCESS_TOKEN');
    config.headers.authorization = `Bearer ${token}`;
    return config;
})

axiosClient.interceptors.response.use((response ) => {
return response;
}, (err) => {
    const { response } = err;
    if (response && response.status === 401) {
        // remove invalid token
        localStorage.removeItem('ACCESS_TOKEN');
    }

    return Promise.reject(err);
})

export default axiosClient;
