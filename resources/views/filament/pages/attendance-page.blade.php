<x-filament::page>
    <div>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
        <div class="container px-4 py-8 mx-auto">

            <!-- Current Time Display -->
            <div class="py-4 text-center">
                <div class="block p-4 w-full rounded-xl shadow-lg dark:bg-gray-800 sm:max-w-md sm:mx-auto">
                    <div class="font-mono text-2xl text-blue-600 dark:text-blue-400" id="currentTime">
                        {{ now()->format('H:i:s') }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ now()->format('l, F j, Y') }}
                    </div>
                </div>
            </div>

            <!-- Attendance Status Cards -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-2">
                <!-- Check In Card -->
                <div class="p-6 bg-white rounded-xl border-l-4 border-green-500 shadow-lg dark:bg-gray-800">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">check in</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                                {{ $checkInTime ?? '--:--' }}
                            </p>
                        </div>
                        <div class="p-3 bg-green-100 rounded-full dark:bg-green-900">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Check Out Card -->
                <div class="p-6 bg-white rounded-xl border-l-4 border-blue-500 shadow-lg dark:bg-gray-800">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">check out</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                                {{ $checkOutTime ?? '--:--' }}
                            </p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-full dark:bg-blue-900">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </div>
                    </div>
                </div>

            </div>

            {{-- <div class="mb-8 text-center"> --}}
                <div class="inline-block my-4 w-full bg-orange-600 rounded-xl shadow-lg dark:bg-orange-800">
                    <button onclick="openAttendanceModal()"
                        class="px-5 py-2.5 w-full text-sm font-medium text-white rounded-lg bg-primary focus:outline-none hover:bg-green-800 focus:ring-4 focus:ring-green-300 dark:hover:bg-green-700 dark:focus:ring-green-800">
                        Presensi
                    </button>
                </div>

            {{-- </div> --}}
            <div class="overflow-hidden bg-white rounded-xl shadow-lg dark:bg-gray-800">
                {{ $this->table }}
            </div>


        </div>
    </div>

    <!-- Enhanced Attendance Modal -->
    <div id="attendanceModal" class="hidden fixed inset-0 z-50">
        <div class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur-sm"></div>
        <div class="flex justify-center items-center p-4 min-h-screen">
            <div class="overflow-hidden relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl dark:bg-gray-800">
                <!-- Modal Header -->
                <div class="flex justify-between items-center p-6 border-b border-gray-200 dark:border-gray-700">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">mark attendance</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">confirm your location and mark attendance</p>
                    </div>
                    <button onclick="closeAttendanceModal()" class="p-2 text-gray-400 rounded-full transition-colors duration-200 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6">
                    <div id="map" class="overflow-hidden mb-6 rounded-xl shadow-lg" style="height: 300px; width: 100%;"></div>

                    <form id="attendanceForm" wire:submit.prevent="submitAttendance">
                        <input type="hidden" wire:model="latitude" name="latitude" id="latitude">
                        <input type="hidden" wire:model="longitude" name="longitude" id="longitude">

                        <!-- Location Info -->
                        <div class="p-4 mb-6 bg-blue-50 rounded-lg dark:bg-blue-900/20">
                            <div class="flex items-center">
                                <svg class="mr-2 w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span class="text-sm text-blue-700 dark:text-blue-300">location detected automatically</span>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Modal Footer -->
                <div class="flex gap-3 justify-end items-center p-6 bg-gray-50 border-t border-gray-200 dark:border-gray-700 dark:bg-gray-700/50">
                    <button type="button" onclick="closeAttendanceModal()" class="px-6 py-3 text-gray-700 bg-white rounded-lg border border-gray-300 transition-colors duration-200 dark:text-gray-300 dark:bg-gray-800 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700">
                        cancel
                    </button>
                    <button type="submit" form="attendanceForm" class="px-6 py-3 text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg transition-all duration-200 transform hover:from-blue-700 hover:to-blue-800 hover:scale-105">
                        mark attendance
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Alerts -->
    @if (session('success'))
        <div class="fixed top-4 right-4 z-50 p-4 text-green-700 bg-green-100 rounded-lg border border-green-400 shadow-lg dark:bg-green-900 dark:border-green-700 dark:text-green-200">
            <div class="flex items-center">
                <svg class="mr-2 w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="fixed top-4 right-4 z-50 p-4 text-red-700 bg-red-100 rounded-lg border border-red-400 shadow-lg dark:bg-red-900 dark:border-red-700 dark:text-red-200">
            <div class="flex items-center">
                <svg class="mr-2 w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                {{ session('error') }}
            </div>
        </div>
    @endif

    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <script>
        // Update current time
        function updateCurrentTime() {
            const now = new Date();
            const timeElement = document.getElementById('currentTime');
            if (timeElement) {
                timeElement.textContent = now.toLocaleTimeString();
            }
        }

        // Update time every second
        setInterval(updateCurrentTime, 1000);

        function openAttendanceModal() {
            document.getElementById('attendanceModal').classList.remove('hidden');
            setTimeout(initMap, 100);
        }

        function closeAttendanceModal() {
            document.getElementById('attendanceModal').classList.add('hidden');
        }

        function refreshPage() {
            window.location.reload();
        }

        function initMap() {
            if (window.attendanceMap) {
                window.attendanceMap.remove();
                window.attendanceMap = null;
            }

            const officeLat = {{ $officeLat }};
            const officeLng = {{ $officeLng }};

            window.attendanceMap = L.map('map').setView([officeLat, officeLng], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(window.attendanceMap);

            // Office marker
            L.marker([officeLat, officeLng], {
                icon: L.divIcon({
                    className: 'custom-div-icon',
                    html: '<div style="background-color: #3B82F6; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>',
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                })
            }).addTo(window.attendanceMap).bindPopup('<b>office location</b>').openPopup();

            // Office radius
            L.circle([officeLat, officeLng], {
                color: '#3B82F6',
                fillColor: '#3B82F6',
                fillOpacity: 0.1,
                radius: 100
            }).addTo(window.attendanceMap);

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    var userLocation = [position.coords.latitude, position.coords.longitude];

                    // User location marker
                    L.marker(userLocation, {
                        icon: L.divIcon({
                            className: 'custom-div-icon',
                            html: '<div style="background-color: #10B981; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>',
                            iconSize: [20, 20],
                            iconAnchor: [10, 10]
                        })
                    }).addTo(window.attendanceMap).bindPopup('<b>your location</b>');

                    window.attendanceMap.setView(userLocation, 15);

                    document.getElementById('latitude').value = position.coords.latitude;
                    document.getElementById('longitude').value = position.coords.longitude;
                    document.getElementById('latitude').dispatchEvent(new Event('input'));
                    document.getElementById('longitude').dispatchEvent(new Event('input'));
                }, function(error) {
                    console.error('Error getting location:', error);
                    alert('Error getting your location: ' + error.message);
                });
            } else {
                alert('Geolocation is not supported by this browser.');
            }
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.fixed.top-4.right-4');
            alerts.forEach(alert => {
                alert.style.display = 'none';
            });
        }, 5000);
    </script>
</x-filament::page>
