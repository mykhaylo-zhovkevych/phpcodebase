import {Link} from 'react-router-dom';
import {useRef, useState} from 'react';
import axiosClient from '../axios-client.js';
import {useStateContext} from '../context/StateContext.js';

export default function Login() {
    const emailRef = useRef();
    const passwordRef = useRef();
    const [errors, setErrors] = useState(null);
    const {setUser, setToken} = useStateContext();

    const onSubmit = (ev) => {
        ev.preventDefault();

        const payload = {
            email: emailRef.current.value,
            password: passwordRef.current.value,
        };

        setErrors(null);

        axiosClient.post('/login', payload)
            .then(({data}) => {
                setUser(data.user);
                setToken(data.token);
            })
            .catch((err) => {
                const response = err.response;

                if (response && response.status === 422) {
                    setErrors(response.data.errors);
                } else if (response && response.data.message) {
                    setErrors({email: [response.data.message]});
                }
            });
    };

    return (
        <>
            <div className="auth-header">
                <h1>Sign in to your account</h1>
                <p>
                    Manage users, dashboard data, and account settings from one
                    place.
                </p>
            </div>

            <form onSubmit={onSubmit}>
                {errors && (
                    <div className="alert">
                        {Object.keys(errors).map((key) => (
                            <p key={key}>{errors[key][0]}</p>
                        ))}
                    </div>
                )}

                <label>
                    Email
                    <input ref={emailRef} type="email" name="email" placeholder="you@example.com" />
                </label>

                <label>
                    Password
                    <input
                        ref={passwordRef}
                        type="password"
                        name="password"
                        placeholder="Enter your password"
                    />
                </label>

                <button className="btn btn-block">Login</button>

                <p className="message">
                    Not registered? <Link to="/signup">Create an account</Link>
                </p>
            </form>
        </>
    );
}
