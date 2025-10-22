<x-app-layout>
    <x-slot name="header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                🤖 {{ __('Edit Chatbot Response #') }}{{ $chatbotResponse->menu_number }}
            </h2>
            <a href="{{ route('admin.chatbot.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                ← Back to List
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('admin.chatbot.update', $chatbotResponse) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Menu Number -->
                        <div class="mb-6">
                            <label for="menu_number" class="block text-sm font-medium text-gray-700 mb-2">
                                Menu Number *
                            </label>
                            <select name="menu_number" id="menu_number" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach($availableNumbers as $number)
                                    <option value="{{ $number }}" {{ old('menu_number', $chatbotResponse->menu_number) == $number ? 'selected' : '' }}>
                                        {{ $number }}
                                    </option>
                                @endforeach
                            </select>
                            @error('menu_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Title -->
                        <div class="mb-6">
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                                Title *
                            </label>
                            <input type="text" name="title" id="title" value="{{ old('title', $chatbotResponse->title) }}" required maxlength="100" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Message -->
                        <div class="mb-6">
                            <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                                Message Text *
                            </label>
                            <textarea name="message" id="message" rows="10" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('message', $chatbotResponse->message) }}</textarea>
                            @error('message')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Footer -->
                        <div class="mb-6">
                            <label for="footer" class="block text-sm font-medium text-gray-700 mb-2">
                                Footer Text
                            </label>
                            <textarea name="footer" id="footer" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Send MENU to continue or EXIT to quit.">{{ old('footer', $chatbotResponse->footer) }}</textarea>
                            @error('footer')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">
                                This footer is <strong>only added when chatbot sends</strong> the message. Quick Responses (agents) will not include this footer.
                            </p>
                            <div class="mt-2 p-3 bg-amber-50 rounded text-sm text-amber-800">
                                <strong>⚠️ Note:</strong> Edit this to add navigation instructions like "Send MENU to continue or EXIT to quit."
                            </div>
                        </div>

                        <!-- Image Picker -->
                        <div class="mb-6">
                            <label for="image_path" class="block text-sm font-medium text-gray-700 mb-2">
                                Image (Optional)
                            </label>
                            <select name="image_path" id="image_path" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" onchange="previewImage()">
                                <option value="">No image</option>
                                <!-- Will be populated by JavaScript -->
                            </select>
                            @error('image_path')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            
                            <!-- Image Preview -->
                            <div id="image-preview" class="mt-3 {{ $chatbotResponse->image_url ? '' : 'hidden' }}">
                                <img id="preview-img" src="{{ $chatbotResponse->image_url ?? '' }}" alt="Preview" class="max-w-xs max-h-48 rounded border">
                            </div>
                            
                            <p class="mt-2 text-xs text-gray-500">
                                Select an image from the library. <a href="{{ route('admin.chatbot.images.index') }}" target="_blank" class="text-blue-600 hover:text-blue-800">🖼️ Manage Image Library</a>
                            </p>
                        </div>

                        <!-- Active Status -->
                        <div class="mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="active" value="1" {{ old('active', $chatbotResponse->active) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700">Active (visible in chatbot menu)</span>
                            </label>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-end space-x-3">
                            <a href="{{ route('admin.chatbot.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                ✅ Update Response
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const currentImagePath = '{{ $chatbotResponse->image_path ?? '' }}';
        
        // Load images from library
        fetch('{{ route('admin.chatbot.images.list') }}')
            .then(response => response.json())
            .then(images => {
                const select = document.getElementById('image_path');
                images.forEach(image => {
                    const option = document.createElement('option');
                    option.value = image.path;
                    option.textContent = image.filename;
                    option.dataset.url = image.url;
                    
                    // Pre-select current image
                    if (image.path === currentImagePath) {
                        option.selected = true;
                    }
                    
                    select.appendChild(option);
                });
            })
            .catch(error => console.error('Error loading images:', error));

        // Preview selected image
        function previewImage() {
            const select = document.getElementById('image_path');
            const preview = document.getElementById('image-preview');
            const previewImg = document.getElementById('preview-img');
            
            if (select.value) {
                const selectedOption = select.options[select.selectedIndex];
                previewImg.src = selectedOption.dataset.url;
                preview.classList.remove('hidden');
            } else {
                preview.classList.add('hidden');
            }
        }
    </script>
</x-app-layout>

