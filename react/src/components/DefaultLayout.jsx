import { Outlet, Navigate, Link } from 'react-router-dom';
import {useStateContext} from "../context/StateContext.js"
import axiosClient from "../axios-client.js";


export default function DefaultLayout() {
    const {user, token, setUser, setToken} = useStateContext();

    if(!token) {
        return <Navigate to="/login" />;
    }

    const onLogout = (en) => {
        en.preventDefault();

        axiosClient.post('/logout')
            .then(() => {
                setUser({});
                setToken(null);
            });
    };

    return (
        <div id="defaultLayout">
            <aside>
                <Link to="/dashboard">DashBoard</Link>
                <Link to="/users">Users</Link>
            </aside>
            <div className='content'>
                <header>
                     <div>
                         Header
                     </div>
                    <div>
                        {user.name}
                        <a href="#" onClick={onLogout} className='bth-logout'>Logout</a>
                    </div>
                </header>
                <main>
                    <Outlet />
                </main>
            </div>
        </div>
    );
}
