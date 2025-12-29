<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pending Users</title>
</head>
<body>

<h2>Pending Registration Requests</h2>

<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Phone</th>
        <th>Role</th>
        <th>Action</th>
    </tr>

    @foreach($users as $user)
        <tr>
            <td>{{ $user->id }}</td>
            <td>{{ $user->first_name }} {{ $user->last_name }}</td>
            <td>{{ $user->phone }}</td>
            <td>{{ $user->role }}</td>
            <td>
                <form method="POST" action="{{ route('admin.users.approve', $user->id) }}" style="display:inline;">
                    @csrf
                    <button type="submit">Approve</button>
                </form>

                <form method="POST" action="{{ route('admin.users.reject', $user->id) }}" style="display:inline;">
                    @csrf
                    <button type="submit">Reject</button>
                </form>
            </td>
        </tr>
    @endforeach

</table>

</body>
</html>
