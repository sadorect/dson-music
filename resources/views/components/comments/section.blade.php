<div class="comments-section mt-8">
  <h3 class="text-xl font-semibold mb-4">Comments</h3>
  
  @auth
<div class="mb-6">
    <form 
    onsubmit="submitComment(event, '{{ class_basename($model) }}', {{ $model->id }})" 
    class="space-y-4"
>
    @csrf
    <textarea 
        name="content" 
        rows="3" 
        class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500"
        placeholder="Add a comment..."
        required
    ></textarea>
    <div class="flex justify-end">
        <button type="submit" class="dson-btn">Post Comment</button>
    </div>
</form>


</div>
@else

  <div class="bg-gray-50 rounded-lg p-4 text-center mb-6">
      <a href="{{ route('login') }}" class="text-red-600 hover:text-red-700">Sign in</a> to join the conversation
  </div>
  @endauth

  <div id="comments-container" class="space-y-6">
    @foreach($model->comments()->with(['user', 'replies.user'])
        ->whereNull('parent_id')
        ->latest()
        ->get() as $comment)
        @include('components.comments.comment', ['comment' => $comment])
    @endforeach
</div>
@push('scripts')
<script type="module">
    import { deleteComment, editComment, toggleReplyForm } from '{{ Vite::asset('resources/js/comments.js') }}';
    window.deleteComment = deleteComment;
    window.editComment = editComment;
    window.toggleReplyForm = toggleReplyForm;
</script>
@endpush


</div>
