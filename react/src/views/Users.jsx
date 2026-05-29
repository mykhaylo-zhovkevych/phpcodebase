import { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import axiosClient from "../axios-client.js";
import {useStateContext} from "../context/StateContext.js";

export function Users() {

    const [users, setUsers] = useState([]);
    const [loading, setLoading] = useState(false);
    const {setNotification} = useStateContext();

    const getUsers = () => {
        setLoading(true);

        axiosClient.get('/users')
            .then(({ data }) => {
                setUsers(data.data);
                setLoading(false);
            })
            .catch(() => {
                setLoading(false);
            });
    }

    useEffect(() => {
        getUsers();
    }, []);

    const onDelete = (u) => {
        if(!window.confirm("Are you sure you wnat delete this user?")) {
            return
        }
        axiosClient.delete(`/users/${u.id}`)
            .then(() => {
                setNotification('User was successfully deleted');
                getUsers()
            })
    }

    return (
        <div>
            <div
                style={{
                    display: "flex",
                    justifyContent: "space-between",
                    alignItems: "center",
                }}
            >
                <h1>Users</h1>

                <button>
                    <Link to="/users/new" className={"bth-add"}>
                        Add new User
                    </Link>
                </button>
            </div>

            {loading && <p>Loading users...</p>}

            {!loading && (
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    {users.map((user) => (
                        <tr key={user.id}>
                            <td>{user.name}</td>
                            <td>{user.email}</td>
                            <td>{user.created_at}</td>
                            <td>
                                <button>
                                    <Link to={`/users/${user.id}`}>Edit</Link>
                                </button>
                                <button onClick={ev => onDelete(user)} className="bth-delete">Delete</button>
                            </td>

                        </tr>
                    ))}
                    </tbody>
                </table>
            )}
        </div>
    );
}

export default Users
