<x-app-layout>
    <x-slot name="header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                🤖 {{ __('Chatbot Response Management') }}
            </h2>
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('admin.chatbot.images.index') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 active:bg-purple-900 focus:outline-none focus:border-purple-900 focus:ring ring-purple-300 disabled:opacity-25 transition ease-in-out duration-150">
                    🖼️ Image Library
                </a>
                <a href="{{ route('admin.chatbot.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                    ➕ Add New Response
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6 flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-600">
                                Manage all chatbot menu responses. Customers text <strong>MENU</strong> to start the chatbot.
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-gray-600">Filter:</span>
                            <div class="inline-flex rounded-lg shadow-sm" role="group">
                                <button type="button" id="filter-customer" class="filter-btn active px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-l-lg hover:bg-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-500" onclick="filterResponses('customer')">
                                    🔵 Customer (1-99)
                                </button>
                                <button type="button" id="filter-agent" class="filter-btn px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-r-lg hover:bg-gray-200 focus:z-10 focus:ring-2 focus:ring-gray-500" onclick="filterResponses('agent')">
                                    🟢 Agent (100-199)
                                </button>
                            </div>
                        </div>
                    </div>

                    @if($responses->isEmpty())
                        <div class="text-center py-12">
                            <div class="text-6xl mb-4">🤖</div>
                            <p class="text-gray-600 mb-4">No chatbot responses yet.</p>
                            <a href="{{ route('admin.chatbot.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                ➕ Create First Response
                            </a>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            #
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Title
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Message Preview
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Image
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($responses as $response)
                                        <tr class="{{ $response->active ? '' : 'opacity-50' }} response-row" data-range="{{ $response->menu_number >= 100 ? 'agent' : 'customer' }}">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full text-white font-bold {{ $response->menu_number >= 100 ? 'bg-green-500' : 'bg-blue-500' }}">
                                                    {{ $response->menu_number }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $response->title }}
                                                </div>
                                                @if($response->template_file)
                                                    <div class="text-xs text-gray-500">
                                                        📄 {{ $response->template_file }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-900 max-w-md truncate">
                                                    {{ Str::limit($response->message_without_media, 100) }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if($response->image_url)
                                                    <a href="{{ $response->image_url }}" target="_blank" class="text-blue-600 hover:text-blue-900">
                                                        🖼️ View
                                                    </a>
                                                @else
                                                    <span class="text-gray-400">No image</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($response->active)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Active
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        Inactive
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('admin.chatbot.edit', $response) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                                    ✏️ Edit
                                                </a>
                                                <form action="{{ route('admin.chatbot.destroy', $response) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this response? This action cannot be undone.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                                        🗑️ Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                            <h3 class="font-semibold text-sm text-blue-900 mb-2">💡 Pro Tips:</h3>
                            <ul class="text-sm text-blue-800 space-y-1">
                                <li>• <span class="inline-block w-4 h-4 bg-blue-500 rounded-full"></span> <strong>Blue (1-99):</strong> Customer self-service menu (visible when they text MENU)</li>
                                <li>• <span class="inline-block w-4 h-4 bg-green-500 rounded-full"></span> <strong>Green (100-199):</strong> Agent-only Quick Responses (hidden from customers)</li>
                                <li>• Images are automatically included with the <code class="bg-blue-100 px-1 rounded">&lt;media&gt;</code> tag</li>
                                <li>• Set responses to "Inactive" to temporarily disable without deleting</li>
                                <li>• Customers can exit the chatbot by texting <strong>EXIT</strong></li>
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentFilter = 'customer';

        function filterResponses(filter) {
            currentFilter = filter;
            
            // Update button states
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active', 'bg-blue-600', 'bg-green-600', 'text-white');
                btn.classList.add('bg-gray-100', 'text-gray-700');
            });
            
            const activeBtn = document.getElementById('filter-' + filter);
            activeBtn.classList.remove('bg-gray-100', 'text-gray-700');
            activeBtn.classList.add('active', 'text-white');
            
            if (filter === 'customer') {
                activeBtn.classList.add('bg-blue-600');
            } else {
                activeBtn.classList.add('bg-green-600');
            }
            
            // Filter rows
            document.querySelectorAll('.response-row').forEach(row => {
                if (row.dataset.range === filter) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            filterResponses('customer');
        });
    </script>
</x-app-layout>

