<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">🖼️ Image Library</h2>
                    <p class="mt-1 text-sm text-gray-600">
                        Manage chatbot images • Upload images with readable filenames
                    </p>
                </div>
                <a href="{{ route('admin.chatbot.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    ← Back to Responses
                </a>
            </div>

            <!-- Success/Error Messages -->
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-md">
                    <p class="text-sm text-green-800">{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-md">
                    <p class="text-sm text-red-800">{{ session('error') }}</p>
                </div>
            @endif

            <!-- Upload Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">📤 Upload New Image</h3>
                    
                    <form action="{{ route('admin.chatbot.images.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="flex items-end space-x-4">
                            <div class="flex-1">
                                <label for="image" class="block text-sm font-medium text-gray-700 mb-2">
                                    Select Image
                                </label>
                                <input type="file" name="image" id="image" accept="image/*" required class="block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-md file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-blue-50 file:text-blue-700
                                    hover:file:bg-blue-100 cursor-pointer">
                                @error('image')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                Upload
                            </button>
                        </div>
                        
                        <p class="mt-2 text-xs text-gray-500">
                            💡 <strong>Tip:</strong> Use descriptive filenames like "skyconnect-diagram.jpg" - the original filename will be preserved!
                            Spaces and special characters will be converted to hyphens.
                        </p>
                        <p class="mt-1 text-xs text-gray-500">
                            Max file size: 5MB • Supported: JPG, PNG, GIF
                        </p>
                    </form>
                </div>
            </div>

            <!-- Image Gallery -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        📚 Image Library ({{ $images->count() }} images)
                    </h3>

                    @if($images->isEmpty())
                        <div class="text-center py-12 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="mt-4">No images uploaded yet</p>
                            <p class="text-sm mt-2">Upload an image above to get started</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($images as $image)
                                <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <!-- Thumbnail -->
                                    <div class="aspect-video bg-gray-100 rounded mb-3 overflow-hidden flex items-center justify-center">
                                        <img src="{{ $image['url'] }}" alt="{{ $image['filename'] }}" class="max-w-full max-h-full object-contain">
                                    </div>
                                    
                                    <!-- Filename -->
                                    <div class="mb-2">
                                        <p class="text-sm font-semibold text-gray-900 break-all">
                                            {{ $image['filename'] }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ $image['size'] }}
                                        </p>
                                    </div>
                                    
                                    <!-- Usage Info -->
                                    @if($image['in_use'])
                                        <div class="mb-3 p-2 bg-blue-50 rounded text-xs">
                                            <strong class="text-blue-800">✓ In Use:</strong>
                                            <ul class="mt-1 text-blue-700">
                                                @foreach($image['used_by'] as $response)
                                                    <li>#{{{ $response->menu_number }}} - {{ $response->title }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @else
                                        <div class="mb-3 p-2 bg-gray-50 rounded text-xs text-gray-600">
                                            Not currently used
                                        </div>
                                    @endif
                                    
                                    <!-- Actions -->
                                    <div class="flex space-x-2">
                                        <a href="{{ $image['url'] }}" target="_blank" class="flex-1 inline-flex justify-center items-center px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-xs font-semibold text-gray-700 hover:bg-gray-200">
                                            👁️ View
                                        </a>
                                        
                                        @if(!$image['in_use'])
                                            <form action="{{ route('admin.chatbot.images.destroy') }}" method="POST" onsubmit="return confirm('Delete this image?');">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="filename" value="{{ $image['filename'] }}">
                                                <button type="submit" class="inline-flex items-center px-3 py-2 bg-red-100 border border-red-300 rounded-md text-xs font-semibold text-red-700 hover:bg-red-200">
                                                    🗑️ Delete
                                                </button>
                                            </form>
                                        @else
                                            <button disabled class="inline-flex items-center px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-xs font-semibold text-gray-400 cursor-not-allowed" title="Cannot delete - image is in use">
                                                🔒 Delete
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

