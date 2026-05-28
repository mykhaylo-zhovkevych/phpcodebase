import { useEffect, useState } from "react";
import axiosClient from "../axios-client.js";

export default function Users() {

    const [users, setUsers] = useState([]);
    const [loading, setLoading] = useState(false);

    const getUsers = () => {
        setLoading(true);

        axiosClient.get('/users')
            .then(({data}) => {
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

    return (
        <div>
            <h1>Users</h1>

            {loading && <p>Loading users...</p>}

            {!loading && (
                <ul>
                    {users.map((user) => (
                        <li key={user.id}>
                            {user.name} - {user.email}
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}
