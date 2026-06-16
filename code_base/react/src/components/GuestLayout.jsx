import {Outlet, Navigate} from "react-router-dom"
import {useStateContext} from '../context/StateContext.js';

export default function GuestLayout() {
    const {token} = useStateContext()

    if (token) {
        return <Navigate to='/users' />
    }
    return (
        <div id="guestLayout">
            <div className="login-signup-form animated fadeInDown">
                <div className="form">
                    <Outlet />
                </div>
            </div>
        </div>
    );
}
