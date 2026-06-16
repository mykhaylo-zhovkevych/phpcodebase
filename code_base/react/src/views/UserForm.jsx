import { useNavigate, useParams } from "react-router-dom";
import { useEffect, useState } from "react";
import axiosClient from "../axios-client.js";
import {useStateContext} from "../context/StateContext.js";

export default function UserForm() {
    const {id} = useParams()
    const navigate = useNavigate();
    const {setNotification} = useStateContext();
    const [loading, setLoading] = useState(false)
    const [errors, setErrors] = useState(null);
    const emptyUser = {
        id: null,
        name: '',
        email: '',
        password: '',
        password_confirmation: ''
    };
    const [user, setUser] = useState(emptyUser)


    useEffect(() => {
        if (id) {
            setLoading(true)
            axiosClient.get(`/users/${id}`)
                .then(({data}) => {
                    setLoading(false)
                    // Overrides after setting
                    setUser({...emptyUser, ...(data)})
                })
                .catch(() => {
                    setLoading(false)
                })
        }
    }, [])

    const onSubmit = (ev) => {
        ev.preventDefault();

        if(user.id) {
            // Copy of the state
            const payload = {...user};
            if (!payload.password) {
                delete payload.password;
                delete payload.password_confirmation;
            }

            axiosClient.put(`/users/${user.id}`, payload)
                .then(() => {
                    // Redirection to /users
                    setNotification('User was successfully updated');
                    navigate('/users');
                })
                .catch(err => {
                    const response = err.response;
                    if (response && response.status == 422) {
                        setErrors(response.data.errors)
                    }
                })
        } else {
            axiosClient
                .post(`/users`, user)
                .then(() => {
                    // Redirection to /users
                    setNotification("User was successfully created");
                    navigate("/users");
                })
                .catch((err) => {
                    const response = err.response;
                    if (response && response.status == 422) {
                        setErrors(response.data.errors);
                    }
                });
        }
    }

    return (
        <>
            {user.id && <h1>Update User: {user.name}</h1>}
            {!user.id && <h1>New User</h1>}
            <div className={"card animated fadInDown"}>
                {loading && <div className="text-center">Loading...</div>}
                {errors && (
                    <div className="alert">
                        {Object.keys(errors).map((key) => (
                            <p key={key}>{errors[key][0]}</p>
                        ))}
                    </div>
                )}
                {!loading && (
                    <form onSubmit={onSubmit}>
                        <input
                            value={user.name}
                            onChange={(ev) =>
                                setUser({ ...user, name: ev.target.value })
                            }
                            placeholder="Name"
                        />
                        <input
                            type="email"
                            value={user.email}
                            onChange={(ev) =>
                                setUser({ ...user, email: ev.target.value })
                            }
                            placeholder="Email"
                        />
                        <input
                            type="password"
                            onChange={(ev) =>
                                setUser({ ...user, password: ev.target.value })
                            }
                            placeholder="Password"
                        />
                        <input
                            type="password"
                            onChange={(ev) =>
                                setUser({
                                    ...user,
                                    password_confirmation: ev.target.value,
                                })
                            }
                            placeholder="Password Confirmation"
                        />
                        <button className="bth">Save</button>
                    </form>
                )}
            </div>
        </>
    );
}
