@extends('contact-approvable::layout')

@section('title', 'Approval Details')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Approval Details</h2>
            <p class="text-sm text-gray-600 mt-1">View approval information and timeline</p>
        </div>
        <div>
            @if($approval->isPending())
                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
            @elseif($approval->isApproved())
                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">Approved</span>
            @else
                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>
            @endif
        </div>
    </div>

    <!-- Approval Information -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Approval Information</h3>
        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-gray-500">Approvable Type</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ class_basename($approval->approvable_type) }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Approvable ID</dt>
                <dd class="mt-1 text-sm text-gray-900">#{{ $approval->approvable_id }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Full Type</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $approval->approvable_type }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Contact</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    <a href="{{ route('contact-approvable.contacts.show', $approval->contact_id) }}" class="text-blue-600 hover:text-blue-800">
                        {{ $approval->contact->name ?? 'N/A' }}
                    </a>
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Status</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    @if($approval->isPending())
                        <span class="text-yellow-600 font-medium">Pending</span>
                    @elseif($approval->isApproved())
                        <span class="text-green-600 font-medium">Approved</span>
                    @else
                        <span class="text-red-600 font-medium">Rejected</span>
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Created At</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $approval->created_at->format('F d, Y H:i') }}</dd>
            </div>
        </dl>
    </div>

    <!-- Approvable Model Info -->
    @if(isset($approval->approvable))
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Approvable Model Details</h3>
            <div class="bg-gray-50 rounded-lg p-4">
                <pre class="text-sm text-gray-700 overflow-x-auto">{{ json_encode($approval->approvable->toArray(), JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
    @endif

    <!-- Approval Records Timeline -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Approval Timeline</h3>
        </div>
        <div class="p-6">
            @if($approval->records->count() > 0)
                <div class="flow-root">
                    <ul class="-mb-8">
                        @foreach($approval->records->sortByDesc('created_at') as $index => $record)
                            <li>
                                <div class="relative pb-8">
                                    @if(!$loop->last)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div>
                                            @if($record->is_approved)
                                                <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center ring-8 ring-white">
                                                    <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                            @else
                                                <div class="h-8 w-8 rounded-full bg-red-100 flex items-center justify-center ring-8 ring-white">
                                                    <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div class="flex-1">
                                                <p class="text-sm text-gray-900">
                                                    <span class="font-medium">{{ $record->user->name ?? 'Unknown User' }}</span>
                                                    @if($record->is_approved)
                                                        <span class="text-green-600">approved</span>
                                                    @else
                                                        <span class="text-red-600">rejected</span>
                                                    @endif
                                                    this approval
                                                </p>
                                                @if($record->comment)
                                                    <p class="mt-2 text-sm text-gray-500">{{ $record->comment }}</p>
                                                @endif
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                {{ $record->created_at->diffForHumans() }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">No approval records yet. This approval is pending.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

