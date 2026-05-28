import {Link} from 'react-router-dom';
import { useRef, useState } from "react";
import {useStateContext} from '../context/StateContext.js';
import axiosClient from '../axios-client.js';


export default function Signup() {
    const nameRef = useRef();
    const emailRef = useRef();
    const passwordRef = useRef();
    const passwordConfirmationRef = useRef();
    const [errors, setErrors] = useState(null);


    const {setUser, setToken} = useStateContext();

    const onSubmit = (ev) => {
        ev.preventDefault();

        const payload = {
            name: nameRef.current.value,
            email: emailRef.current.value,
            password: passwordRef.current.value,
            // Larvel specific
            password_confirmation: passwordConfirmationRef.current.value,
        }
        axiosClient.post('/signup', payload)
            .then(({data}) => {
                setUser(data.user)
                setToken(data.token)
            })
            .catch(err => {
                const response = err.response;
                // unprocessable content
                if (response && response.status === 422) {
                    setErrors(response.data.errors);
                }
                else if (response && response.data.message) {
                        setErrors({ email: [response.data.message] });
                }
            })
    };

    return (
        <>
            <div className="auth-header">
                <h1>Start your workspace</h1>
                <p>
                    Create your account to manage users, dashboard data, and
                    settings.
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
                    Username
                    <input
                        ref={nameRef}
                        type="text"
                        name="name"
                        placeholder="Your name"
                    />
                </label>

                <label>
                    Email
                    <input
                        ref={emailRef}
                        type="email"
                        name="email"
                        placeholder="you@example.com"
                    />
                </label>

                <label>
                    Password
                    <input
                        ref={passwordRef}
                        type="password"
                        name="password"
                        placeholder="Create a password"
                    />
                </label>

                <label>
                    Confirm password
                    <input
                        ref={passwordConfirmationRef}
                        type="password"
                        name="password_confirmation"
                        placeholder="Repeat your password"
                    />
                </label>

                <button className="btn btn-block">Sign up</button>

                <p className="message">
                    Already registered? <Link to="/login">Sign in</Link>
                </p>
            </form>
        </>
    );
}
