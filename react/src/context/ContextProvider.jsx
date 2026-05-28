import {useEffect, useState} from 'react';
import {StateContext} from './StateContext.js';

// localStorage keyname
const ACCESS_TOKEN_KEY = 'ACCESS_TOKEN';
//const  TEMP_ACCESS_TOKEN = 123;
export const ContextProvider = ({children}) => {
    const [user, setUser] = useState({});
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

    return <StateContext.Provider value={{
        user,
        token,
        setUser,
        setToken
    }}>
        {children}
    </StateContext.Provider>;
}
