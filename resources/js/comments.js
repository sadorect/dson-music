export const submitComment = async (event, type, id) => {
    event.preventDefault();
    const form = event.target;
    const content = form.querySelector('textarea').value;
    
    try {
        const response = await fetch('/comments', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ 
                content,
                commentable_type: type.toLowerCase(),
                commentable_id: id
            })
        });
        
        const data = await response.json();
        console.log('Comment response:', data); // Debug response
        
         // Insert new comment at the top
         const commentHtml = renderComment(data.comment);
         commentsContainer.insertAdjacentHTML('afterbegin', commentHtml);
         /*
        const commentsContainer = document.getElementById('comments-container');
        commentsContainer.insertAdjacentHTML('afterbegin', renderComment(data.comment));
        */
        form.reset();
        
    } catch (error) {
        console.error('Error posting comment:', error);
    }
};


const renderComment = (comment) => {
    return `
        <div class="comment-${comment.id} bg-white rounded-lg p-4 shadow-sm">
            <div class="flex items-start space-x-3">
                <img src="${comment.user.profile_photo_url}" alt="" class="w-10 h-10 rounded-full">
                <div class="flex-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="font-medium">${comment.user.name}</h4>
                            <span class="text-sm text-gray-500">Just now</span>
                        </div>
                    </div>
                    <div class="mt-2 text-gray-700">
                        ${comment.content}
                    </div>
                </div>
            </div>
        </div>
    `;
};

export const submitReply = async (event, type, id, parentId) => {
    event.preventDefault();
    const form = event.target;
    const content = form.querySelector('textarea').value;
    
    try {
        const response = await fetch('/comments', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ 
                content,
                commentable_type: type,
                commentable_id: id,
                parent_id: parentId 
            })
        });
        
        const data = await response.json();
        location.reload();
    } catch (error) {
        console.error('Error posting reply:', error);
    }
};

export const toggleReplyForm = (commentId) => {
    const form = document.getElementById(`reply-form-${commentId}`);
    form.classList.toggle('hidden');
};

export const deleteComment = async (commentId) => {
    if (!confirm('Are you sure you want to delete this comment?')) return;
    
    try {
        const response = await fetch(`/comments/${commentId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        document.querySelector(`.comment-${commentId}`).remove();
    } catch (error) {
        console.error('Error deleting comment:', error);
    }
};

export const togglePin = async (commentId) => {
    try {
        const response = await fetch(`/comments/${commentId}/pin`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        location.reload();
    } catch (error) {
        console.error('Error toggling pin:', error);
    }
};
