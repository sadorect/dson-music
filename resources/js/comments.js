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
                commentable_type: type,
                commentable_id: id
            })
        });
        
        const data = await response.json();
        location.reload();
    } catch (error) {
        console.error('Error posting comment:', error);
    }
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
