<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-chat-bubble-left-right class="w-5 h-5" />
                Percakapan
            </div>
        </x-slot>

        <div class="space-y-4">
            {{-- Chat Messages --}}
            <div class="bg-gray-50 dark:bg-gray-900 rounded-xl p-4 max-h-[400px] overflow-y-auto space-y-4" id="chat-container">
                @forelse($responses as $response)
                    @if($response->user)
                        @php
                            $isCurrentUser = $response->user_id === auth()->id();
                            $isAdmin = $response->user->hasRole('super_admin');
                        @endphp
                        <div class="flex {{ $isCurrentUser ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[80%]">
                                {{-- Avatar & Name --}}
                                <div class="flex items-center gap-2 mb-1 {{ $isCurrentUser ? 'justify-end' : 'justify-start' }}">
                                    @if(!$isCurrentUser)
                                        <div class="w-7 h-7 rounded-full bg-primary-500 flex items-center justify-center text-white text-xs font-bold shrink-0">
                                            {{ strtoupper(substr($response->user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <span class="text-xs font-medium text-gray-600 dark:text-gray-400">
                                        {{ $response->user->name }}
                                        @if($isAdmin)
                                            <span class="inline-flex items-center ml-1 px-1.5 py-0.5 rounded text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200">
                                                Admin IT
                                            </span>
                                        @endif
                                    </span>
                                    @if($response->is_internal_note)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                            <x-heroicon-m-lock-closed class="w-3 h-3 mr-0.5" />
                                            Catatan Internal
                                        </span>
                                    @endif
                                </div>

                                {{-- Message Bubble --}}
                                <div class="rounded-2xl px-4 py-2.5 {{ $isCurrentUser ? 'bg-primary-500 text-white rounded-br-sm' : 'bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-bl-sm shadow-sm border border-gray-200 dark:border-gray-700' }}">
                                    <div class="text-sm leading-relaxed">
                                        {!! $response->message !!}
                                    </div>
                                </div>

                                {{-- Timestamp --}}
                                <div class="text-xs text-gray-400 mt-1 {{ $isCurrentUser ? 'text-right' : 'text-left' }}">
                                    {{ $response->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="text-center py-12 text-gray-500">
                        <x-heroicon-o-chat-bubble-left-right class="w-16 h-16 mx-auto mb-3 opacity-30" />
                        <p class="font-medium">Belum ada percakapan</p>
                        <p class="text-sm">Mulai percakapan dengan mengirim pesan</p>
                    </div>
                @endforelse
            </div>

            {{-- Input Form --}}
            @if($ticket->status !== 'closed')
                <form wire:submit="sendMessage">
                    <div class="flex gap-3 items-end">
                        <div class="flex-1">
                            <textarea
                                wire:model="message"
                                rows="2"
                                class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 resize-none text-sm"
                                placeholder="Tulis pesan Anda di sini..."
                            ></textarea>
                            @error('message')
                                <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <button
                            type="submit"
                            class="px-4 py-2.5 bg-primary-500 text-white rounded-xl hover:bg-primary-600 transition-colors flex items-center gap-2 font-medium text-sm"
                        >
                            <span class="hidden sm:inline">Kirim</span>
                            <x-heroicon-o-paper-airplane class="w-5 h-5" />
                        </button>
                    </div>
                </form>
            @else
                <div class="text-center py-6 text-gray-500 bg-gray-100 dark:bg-gray-800 rounded-xl">
                    <x-heroicon-o-lock-closed class="w-6 h-6 mx-auto mb-2 opacity-50" />
                    <p class="text-sm font-medium">Tiket sudah ditutup</p>
                    <p class="text-xs">Tidak dapat mengirim pesan baru</p>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

<script>
    document.addEventListener('livewire:initialized', () => {
        const container = document.getElementById('chat-container');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    });
</script>
