@extends('contact-approvable::layout')

@section('title', 'Settings')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Settings</h2>
            <p class="text-sm text-gray-600 mt-1">Package configuration and settings</p>
        </div>
    </div>

    <!-- Configuration Display -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Current Configuration</h3>
        </div>
        <div class="p-6">
            <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Admin Route</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ config('contact-approvable.route', 'contact-approvable') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">User Model</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ config('contact-approvable.user_model', 'App\\Models\\User') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Notifications Enabled</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if(config('contact-approvable.notifications.enabled', true))
                            <span class="text-green-600">Yes</span>
                        @else
                            <span class="text-red-600">No</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Notification Channels</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ implode(', ', config('contact-approvable.notifications.channels', ['mail', 'database'])) }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Events Enabled</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if(config('contact-approvable.events.enabled', true))
                            <span class="text-green-600">Yes</span>
                        @else
                            <span class="text-red-600">No</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Items Per Page</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ config('contact-approvable.admin.per_page', 15) }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Table Names -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Database Table Names</h3>
        </div>
        <div class="p-6">
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @foreach(config('contact-approvable.table_names', []) as $key => $value)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </div>

    <!-- Environment Variables Guide -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Environment Variables</h3>
        </div>
        <div class="p-6">
            <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                <div>
                    <p class="text-sm font-medium text-gray-700 mb-1">CONTACT_APPROVABLE_ROUTE</p>
                    <p class="text-xs text-gray-500">Customize the admin interface route (default: contact-approvable)</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-700 mb-1">CONTACT_APPROVABLE_USER_MODEL</p>
                    <p class="text-xs text-gray-500">Customize the user model class (default: App\Models\User)</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-700 mb-1">CONTACT_APPROVABLE_NOTIFICATIONS_ENABLED</p>
                    <p class="text-xs text-gray-500">Enable or disable notifications (default: true)</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-700 mb-1">CONTACT_APPROVABLE_ADMIN_PER_PAGE</p>
                    <p class="text-xs text-gray-500">Items per page in list views (default: 15)</p>
                </div>
            </div>
            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-sm text-blue-800">
                    <strong>Note:</strong> To modify these settings, edit your <code class="bg-blue-100 px-1 rounded">.env</code> file or publish the configuration file using:
                </p>
                <code class="block mt-2 text-xs bg-blue-100 p-2 rounded text-blue-900">
                    php artisan vendor:publish --tag=contact-approvable-config
                </code>
            </div>
        </div>
    </div>
</div>
@endsection

