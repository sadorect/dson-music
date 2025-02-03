export const submitComment = async (event, type, id) => {
    event.preventDefault();
    const form = event.target;
    const content = form.querySelector('textarea').value;
    const commentsContainer = document.getElementById('comments-container');

    try {
        const response = await fetch(`/tracks/${id}/comments`, {
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
        
        // Add success notification
        const notification = document.createElement('div');
        notification.className = 'bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4';
        notification.textContent = data.message;
        form.insertAdjacentElement('beforebegin', notification);
        
        // Remove notification after 3 seconds
        setTimeout(() => notification.remove(), 3000);

         // Insert new comment at the top
         const commentHtml = renderComment(data.comment);
         commentsContainer.insertAdjacentHTML('afterbegin', commentHtml);
         /*
        const commentsContainer = document.getElementById('comments-container');
        commentsContainer.insertAdjacentHTML('afterbegin', renderComment(data.comment));
        */

        // Reset form
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
    const repliesContainer = document.getElementById(`replies-${parentId}`);

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
        const replyHtml = renderComment(data.comment);
        repliesContainer.insertAdjacentHTML('beforeend', replyHtml);
        form.reset();
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
        const token = document.querySelector('meta[name="csrf-token"]').content;
        const response = await fetch(`/delete/comments/${commentId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            const commentElement = document.querySelector(`.comment-${commentId}`);
            commentElement.remove();
            
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded';
            notification.textContent = 'Comment deleted successfully';
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }
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


export const editComment = async (commentId) => {
    const commentElement = document.querySelector(`.comment-${commentId}`);
    const contentElement = commentElement.querySelector('.comment-content');
    const currentContent = contentElement.textContent.trim();
    
    const textarea = document.createElement('textarea');
    textarea.value = currentContent;
    textarea.className = 'w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500';
    
    contentElement.replaceWith(textarea);
    
    textarea.focus();
    
    textarea.addEventListener('blur', async () => {
        const newContent = textarea.value.trim();
        if (newContent !== currentContent) {
            try {
                const response = await fetch(`/comments/${commentId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ content: newContent })
                });
                
                const data = await response.json();
                contentElement.textContent = newContent;
            } catch (error) {
                console.error('Error updating comment:', error);
            }
        }
        textarea.replaceWith(contentElement);
    });
};

