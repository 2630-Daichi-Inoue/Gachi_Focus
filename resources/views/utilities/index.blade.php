@extends('layout.app')

@section('content')
{{-- modal --}}
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

<div class="max-w-5xl mx-auto py-10" x-data="utilityPage()">
    <h1 class="text-3xl font-bold mb-6">Category List</h1>

    {{-- flash --}}
    @if(session('success'))
        <div class="mb-4 rounded bg-green-50 border border-green-200 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="mb-4 rounded bg-red-50 border border-red-200 px-4 py-3 text-red-800">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- form to add--}}
    <form class="flex gap-3 mb-6" method="POST" action="{{ route('utilities.store') }}">
        @csrf
        <input name="name" placeholder="Add Utility" class="flex-1 rounded border px-3 py-2"
               value="{{ old('name') }}">
        <button class="rounded bg-gray-800 px-4 py-2 text-white bg-gray-700 hover:bg-gray-600 transition">+ add</button>
    </form>

    {{-- show--}}
    <div class="overflow-hidden rounded border">
        <table class="w-full bg-white">
            <thead class="bg-gray-50 text-gray-500">
                <tr>
                    <th class="px-5 py-3 text-left w-2/3">Category</th>
                    <th class="px-5 py-3 text-center">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($utilities as $u)
                    <tr>
                        <td class="px-5 py-4 text-lg">{{ $u->name }}</td>
                        <td class="px-5 py-4">
                            <div class="flex justify-center gap-4">
                                <button
                                    class="w-24 inline-flex items-center justify-center rounded bg-emerald-600 py-2 text-white hover:bg-emerald-700 transition"
                                    @click="openEdit({{ $u->id }}, '{{ e($u->name) }}')">
                                    Edit
                                </button>

                                <button
                                    class="w-24 inline-flex items-center justify-center rounded border-2 border-red-300 py-2 text-red-700 hover:bg-red-50 transition"
                                    @click="openDelete({{ $u->id }}, '{{ e($u->name) }}')">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td class="px-5 py-6 text-gray-500" colspan="2">No tags yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $utilities->links() }}</div>

    {{-- edit modal--}}
    <div x-show="showEdit" x-cloak class="fixed inset-0 z-50 grid place-items-center bg-black/40">
        <div class="w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
            <h2 class="mb-4 text-2xl font-bold">Edit category</h2>

            <form :action="editAction" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <input type="text" name="name" class="w-full rounded border px-3 py-2" x-model="editName">
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" class="px-4 py-2 rounded border" @click="showEdit=false">Cancel</button>
                    <button class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">Edit</button>
                </div>
            </form>
        </div>
    </div>

    {{-- delete modal --}}
    <div x-show="showDelete" x-cloak class="fixed inset-0 z-50 grid place-items-center bg-black/40">
        <div class="w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
            <h2 class="mb-4 text-2xl font-bold">Are you sure you want to delete?</h2>
            <p class="text-gray-600 mb-6" x-text="editName"></p>
            <form :action="deleteAction" method="POST" class="flex justify-end gap-3">
                @csrf @method('DELETE')
                <button type="button" class="px-4 py-2 rounded border" @click="showDelete=false">Cancel</button>
                <button class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">Delete</button>
            </form>
        </div>
    </div>
</div>

<script>
function utilityPage() {
    return {
        showEdit: false,
        showDelete: false,
        editId: null,
        editName: '',
        get editAction() { return this.editId ? `{{ url('/utilities') }}/${this.editId}` : '#'; },
        get deleteAction() { return this.editAction; },
        openEdit(id, name) {
            this.editId = id; this.editName = name; this.showEdit = true;
        },
        openDelete(id, name) {
            this.editId = id; this.editName = name; this.showDelete = true;
        }
    }
}
</script>
@endsection
