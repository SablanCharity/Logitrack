
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Capstone Audit History | LogiTrack</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 24px;
            background: #f4f6f9;
            color: #1f2937;
        }

        .container {
            max-width: 1200px;
            margin: auto;
        }

        h1 {
            margin-bottom: 8px;
        }

        .subtitle {
            color: #64748b;
            margin-bottom: 24px;
        }

        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
            padding: 16px;
            background: white;
            border-radius: 8px;
        }

        input, select, button {
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 5px;
            font-size: 14px;
        }

        button {
            background: #1d4ed8;
            color: white;
            border: none;
            cursor: pointer;
        }

        .table-wrapper {
            overflow-x: auto;
            background: white;
            border-radius: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 13px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f8fafc;
        }

        .action {
            font-weight: bold;
            color: #1d4ed8;
        }

        .empty {
            padding: 24px;
            text-align: center;
            color: #64748b;
        }

        .pagination {
            margin-top: 20px;
        }

        a {
            color: #1d4ed8;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Capstone Audit History</h1>

        <p class="subtitle">
            Review capstone submissions, verification, returns, and approvals.
        </p>

        <form method="GET"
              action="{{ route('admin.capstone-audits') }}"
              class="filters">

            <input
                type="text"
                name="search"
                placeholder="Search action, user ID, capstone ID..."
                value="{{ request('search') }}"
            >

            <select name="action">
                <option value="">All actions</option>

                @foreach ($actions as $action)
                    <option
                        value="{{ $action }}"
                        @selected(request('action') === $action)
                    >
                        {{ ucwords(str_replace('_', ' ', $action)) }}
                    </option>
                @endforeach
            </select>

            <button type="submit">Filter</button>

            <a href="{{ route('admin.capstone-audits') }}">
                Clear
            </a>
        </form>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date and time</th>
                        <th>User</th>
                        <th>Capstone ID</th>
                        <th>Action</th>
                        <th>Description</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($audits as $audit)
                        <tr>
                            <td>{{ $audit->id }}</td>

                            <td>
                                {{ $audit->created_at?->format('M d, Y h:i A') ?? 'N/A' }}
                            </td>

                            <td>
                                {{ $audit->user
    ? trim($audit->user->first_name . ' ' . $audit->user->last_name)
    : 'Deleted or unavailable user' }}
                                <br>
                                <small>User ID: {{ $audit->user_id ?? 'N/A' }}</small>
                            </td>

                            <td>
                                {{ $audit->capstone_project_id ?? 'N/A' }}
                            </td>

                            <td class="action">
                                {{ ucwords(str_replace('_', ' ', $audit->action)) }}
                            </td>

                            <td>{{ $audit->description ?? 'No description' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty">
                                No audit records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">
            {{ $audits->links() }}
        </div>
    </div>
</body>
</html>
