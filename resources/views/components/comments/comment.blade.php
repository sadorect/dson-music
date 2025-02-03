<div class="comment-{{ $comment->id }} bg-white rounded-lg p-4 shadow-sm">
  <div class="flex items-start space-x-3">
      <img src="{{ $comment->user->profile_photo_url }}" alt="" class="w-10 h-10 rounded-full">
      <div class="flex-1">
          <div class="flex items-center justify-between">
              <div>
                  <h4 class="font-medium">{{ $comment->user->name }}</h4>
                  <span class="text-sm text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
              </div>
              @if($comment->is_pinned)
                  <span class="text-sm text-green-600 flex items-center">
                      <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                          <path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"/>
                      </svg>
                      Pinned
                  </span>
              @endif
          </div>
          
          <div class="mt-2 text-gray-700">
              {{ $comment->content }}
          </div>

          <div class="mt-3 flex items-center space-x-4 text-sm">
              <button onclick="toggleReplyForm({{ $comment->id }})" class="text-gray-500 hover:text-gray-700">
                  Reply
              </button>
              @can('update', $comment)
                  <button onclick="editComment({{ $comment->id }})" class="text-gray-500 hover:text-gray-700">
                      Edit
                  </button>
              @endcan
              @can('delete', $comment)
                  <button onclick="deleteComment({{ $comment->id }})" class="text-red-500 hover:text-red-700">
                      Delete
                  </button>
              @endcan
              @can('pin', $comment)
                  <button onclick="togglePin({{ $comment->id }})" class="text-gray-500 hover:text-gray-700">
                      {{ $comment->is_pinned ? 'Unpin' : 'Pin' }}
                  </button>
              @endcan
          </div>

          <div id="reply-form-{{ $comment->id }}" class="hidden mt-4">
              <form onsubmit="submitReply(event, '{{ $type }}', {{ $model->id }}, {{ $comment->id }})" class="space-y-3">
                  <textarea 
                      rows="2" 
                      class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500"
                      placeholder="Write a reply..."></textarea>
                  <div class="flex justify-end space-x-2">
                      <button type="button" onclick="toggleReplyForm({{ $comment->id }})" class="text-gray-500">
                          Cancel
                      </button>
                      <button type="submit" class="dson-btn-sm">Reply</button>
                  </div>
              </form>
          </div>

          @if($comment->replies->count() > 0)
              <div class="mt-4 space-y-4 pl-6 border-l-2 border-gray-100">
                  @foreach($comment->replies as $reply)
                      @include('components.comments.comment', ['comment' => $reply, 'type' => $type, 'model' => $model])
                  @endforeach
              </div>
          @endif
      </div>
  </div>
</div>

