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
                    <div class="mb-4">
                        <p class="text-sm text-gray-600">
                            Manage all chatbot menu responses. Customers text <strong>MENU</strong> to start the chatbot.
                        </p>
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
                                        <tr class="{{ $response->active ? '' : 'opacity-50' }}">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $response->menu_number }}
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
                                <li>• Menu numbers 1-99 are available for chatbot responses</li>
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
</x-app-layout>

