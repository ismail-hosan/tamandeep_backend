@extends('backend.app')

@section('title', 'Show User')

@section('content')

<div class="content-wrapper">
    <div class="row">
        <!-- User Details Section -->
        <div class="col-lg-4 col-md-5 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">User Details</h4>
                </div>
                <div class="card-body">
                    <div class="media">
                        <img src="{{ asset($user->avater) }}" class="mr-3 rounded-circle" alt="Avatar" width="80">
                        <div class="media-body">
                            <h5 class="mt-0">{{ $user->name }}</h5>
                            <p><strong>Username:</strong> {{ $user->username }}</p>
                            <p><strong>Email:</strong> {{ $user->email }}</p>
                            <p><strong>Phone:</strong> {{ $user->phone }}</p>
                            <p><strong>Status:</strong> {{ ucfirst($user->status) }}</p>
                            <p><strong>Role:</strong> {{ ucfirst($user->role) }}</p>
                            <p><strong>Account Status:</strong> {{ $user->is_block == 0 ? 'Blocked' : 'Active' }}</p>
                            <p><strong>Last Login at:</strong> {{ $user->last_login_at->format('d-m-y, H:i:s') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Videos Section -->
        <div class="col-lg-8 col-md-7 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">User Videos</h4>
                </div>
                <div class="card-body">
                    @if ($user->videos->isEmpty())
                        <p class="text-center">No videos uploaded yet.</p>
                    @else
                        <div class="row">
                            @foreach ($user->videos as $video)
                                <div class="col-md-4 mb-4">
                                    <div class="card">
                                        <img src="{{ asset($video->thumbnail) }}" class="card-img-top" alt="Video Thumbnail">
                                        <div class="card-body">
                                            <h5 class="card-title">{{ $video->title }}</h5>
                                            <p class="card-text">{{ Str::limit($video->description, 100) }}</p>
                                            <p><strong>Type:</strong> {{ ucfirst($video->type) }}</p>
                                            <p><strong>Status:</strong> {{ ucfirst($video->status) }}</p>
                                            <a href="{{ $video->vimeo_path }}" target="_blank" class="btn btn-primary btn-sm">View on Vimeo</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Friends Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">User's Friends</h4>
                </div>
                <div class="card-body">
                    @if ($user->friends->isEmpty())
                        <p class="text-center">No friends found.</p>
                    @else
                        <ul class="list-group">
                            @foreach ($user->friends as $friend)
                                <li class="list-group-item">
                                    <strong>{{ $friend->name }}</strong> ({{ $friend->username }})
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Subscribers Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">User's Subscribers</h4>
                </div>
                <div class="card-body">
                    @if ($user->subscribers->isEmpty())
                        <p class="text-center">No subscribers found.</p>
                    @else
                        <ul class="list-group">
                            @foreach ($user->subscribers as $subscriber)
                                <li class="list-group-item">
                                    <strong>{{ $subscriber->name }}</strong> ({{ $subscriber->username }})
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
