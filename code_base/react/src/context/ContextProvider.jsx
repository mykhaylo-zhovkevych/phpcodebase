import {useEffect, useState} from 'react';
import {StateContext} from './StateContext.js';

// localStorage keyname
const ACCESS_TOKEN_KEY = 'ACCESS_TOKEN';
//const  TEMP_ACCESS_TOKEN = 123;
export const ContextProvider = ({children}) => {
    const [user, setUser] = useState({});
    const [notification, _setNotification] = useState(null);
    // raw/internal setter
    const [token, _setToken] = useState(
        localStorage.getItem(ACCESS_TOKEN_KEY),
        //localStorage.getItem(ACCESS_TOKEN_KEY) || TEMP_ACCESS_TOKEN,
    );

    const setToken = (token) => {
        _setToken(token);
        if (token) {
            localStorage.setItem(ACCESS_TOKEN_KEY, token);
        } else {
            localStorage.removeItem(ACCESS_TOKEN_KEY);
        }
    };

    const setNotification = (message) => {
        _setNotification(message);
        setTimeout(() => {
            _setNotification(null);
        }, 5000);
    };

    return <StateContext.Provider value={{
        user,
        token,
        setUser,
        setToken,
        notification,
        setNotification
    }}>
        {children}
    </StateContext.Provider>;
}
