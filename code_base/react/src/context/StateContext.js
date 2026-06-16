import {createContext} from 'react';
import { useContext } from "react";

export const StateContext = createContext({
    user: null,
    token: null,
    setUser: () => {},
    setToken: () => {},
    notification: null,
    setNotification: () => {}
});

export const useStateContext = () => useContext(StateContext);
