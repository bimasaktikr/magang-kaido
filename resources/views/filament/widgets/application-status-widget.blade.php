<x-filament::widget>
    @php
        $payload = $this->getApplicationStatus();
        $fmt = fn ($d) => $d ? \Illuminate\Support\Carbon::parse($d)->format('d M Y') : null;
    @endphp

    {{-- No application --}}
    @if (! $payload)
        <div class="flex gap-4 items-center p-6 bg-white rounded-xl shadow dark:bg-gray-900">
            <div class="flex justify-center items-center w-14 h-14 bg-gray-100 rounded-full dark:bg-gray-800">
                <svg class="w-8 h-8 text-gray-600 dark:text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3" />
                </svg>
            </div>
            <div>
                <div class="mb-1 text-lg font-semibold text-gray-800 dark:text-gray-100">Belum Ada Aplikasi</div>
                <div class="text-sm text-gray-600 dark:text-gray-300">Silakan ajukan aplikasi magang untuk memulai proses.</div>
            </div>
        </div>
    @else
        @php
            $status = $payload['status'] ?? 'diproses';
            $isAccepted  = $status === 'diterima';
            $isRejected  = $status === 'ditolak';
            $isHold      = $status === 'hold';
            $isProcessing= $status === 'diproses';
            $missing     = $this->missingDocs();
            $canAccept   = $this->canAccept();
        @endphp

        {{-- diterima --}}
        @if ($isAccepted)
            <div class="flex gap-4 items-center p-6 bg-white rounded-xl shadow dark:bg-gray-900">
                <div class="flex justify-center items-center w-14 h-14 bg-green-100 rounded-full dark:bg-green-900">
                    <svg class="w-8 h-8 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <div class="mb-1 text-lg font-semibold text-gray-800 dark:text-gray-100">Status Aplikasi</div>
                    <div class="text-base font-bold text-green-700 dark:text-green-300">Diterima</div>
                    @if ($payload['accepted_start'] || $payload['accepted_end'])
                        <div class="mt-1 text-xs text-gray-500">
                            Periode: <span class="font-medium">
                                {{ $fmt($payload['accepted_start']) }} – {{ $fmt($payload['accepted_end']) }}
                            </span>
                            <span class="ml-2 text-xs text-gray-400">
                                (role: {{ auth()->user()?->getRoleNames()?->first() ?? 'user' }})
                            </span>
                        </div>
                    @endif
                </div>
            </div>

        {{-- ditolak --}}
        @elseif ($isRejected)
            <div class="flex gap-4 items-start p-6 bg-white rounded-xl shadow dark:bg-gray-900">
                <div class="flex justify-center items-center w-14 h-14 bg-red-100 rounded-full dark:bg-red-900">
                    <svg class="w-8 h-8 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <div>
                    <div class="mb-1 text-lg font-semibold text-gray-800 dark:text-gray-100">Status Aplikasi</div>
                    <div class="text-base font-bold text-red-700 dark:text-red-300">Ditolak</div>
                    @if (!empty($payload['rejection_reason']))
                        <div class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                            Alasan: <span class="font-medium">{{ $payload['rejection_reason'] }}</span>
                        </div>
                    @endif
                </div>
            </div>

        {{-- hold --}}
        @elseif ($isHold)
            <div class="flex flex-col gap-4 p-6 bg-white rounded-xl shadow dark:bg-gray-900">
                <div class="flex gap-4 items-center">
                    <div class="flex justify-center items-center w-14 h-14 bg-yellow-100 rounded-full dark:bg-yellow-900">
                        <svg class="w-8 h-8 text-yellow-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" />
                        </svg>
                    </div>
                    <div>
                        <div class="mb-1 text-lg font-semibold text-gray-800 dark:text-gray-100">Menunggu Konfirmasi</div>
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            Periode: <span class="font-medium">
                                {{ $fmt($payload['accepted_start']) }} – {{ $fmt($payload['accepted_end']) }}
                            </span>
                        </div>
                        @if (!empty($payload['hold_reason']))
                            <div class="mt-1 text-xs text-gray-500">
                                Alasan hold: {{ str($payload['hold_reason'])->replace('_',' ')->title() }}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Checklist when missing --}}
                @if (count($missing))
                    <div class="text-xs text-red-600">
                        Dokumen belum lengkap:
                        <ul class="ml-5 list-disc">
                            @foreach ($missing as $m)
                                <li>{{ str($m)->replace('_',' ')->title() }}</li>
                            @endforeach
                        </ul>
                        <div class="mt-1">
                            <a href="{{ route('filament.admin.pages.application-documents') }}" class="text-blue-600 hover:underline">
                                Upload dokumen sekarang
                            </a>
                        </div>
                    </div>
                @endif

                <div class="flex flex-wrap gap-3">
                    <button
                        type="button"
                        wire:click="acceptApplication({{ (int) $payload['id'] }})"
                        @disabled(! $canAccept)
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        Terima
                    </button>

                    @if ($showRejectForm && $rejectingApplicationId === $payload['id'])
                        <div class="flex flex-col gap-2 w-full sm:w-96">
                            <input
                                type="text"
                                wire:model.defer="note"
                                class="block px-3 py-2 w-full text-sm text-gray-900 bg-white rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500"
                                placeholder="Alasan penolakan"
                            />
                            @error('note')
                                <div class="text-xs text-red-600">{{ $message }}</div>
                            @enderror
                            <div class="flex gap-2">
                                <button
                                    type="button"
                                    wire:click="rejectApplication"
                                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">
                                    Tolak
                                </button>
                                <button
                                    type="button"
                                    wire:click="$set('showRejectForm', false)"
                                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-800 bg-gray-100 rounded-lg dark:bg-gray-800 dark:text-gray-100">
                                    Batal
                                </button>
                            </div>
                        </div>
                    @else
                        <button
                            type="button"
                            wire:click="openRejectForm({{ (int) $payload['id'] }})"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">
                            Tolak
                        </button>
                    @endif
                </div>
            </div>

        {{-- diproses / others --}}
        @else
            <div class="flex flex-col gap-3 p-6 bg-white rounded-xl shadow dark:bg-gray-900">
                <div class="flex gap-4 items-center">
                    <div class="flex justify-center items-center w-14 h-14 bg-blue-100 rounded-full dark:bg-blue-900">
                        <svg class="w-8 h-8 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3" />
                        </svg>
                    </div>
                    <div>
                        <div class="mb-1 text-lg font-semibold text-gray-800 dark:text-gray-100">Status Aplikasi</div>
                        <div class="text-base font-bold text-blue-700 dark:text-blue-300">{{ ucfirst($status) }}</div>
                        <div class="mt-1 text-xs text-gray-500">Aplikasi Anda sedang diproses oleh kantor.</div>
                    </div>
                </div>

                @if (count($missing))
                    <div class="text-xs text-red-600">
                        Dokumen belum lengkap:
                        <ul class="ml-5 list-disc">
                            @foreach ($missing as $m)
                                <li>{{ str($m)->replace('_',' ')->title() }}</li>
                            @endforeach
                        </ul>
                        <div class="mt-1">
                            <a href="{{ route('filament.pages.application-documents') }}" class="text-blue-600 hover:underline">
                                Upload dokumen sekarang
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    @endif
</x-filament::widget>
