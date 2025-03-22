document.addEventListener('DOMContentLoaded', function() {
  const logoInputs = document.querySelectorAll('input[type="file"][name^="logo_"], input[type="file"][name="favicon"]');
  
  logoInputs.forEach(input => {
      const previewId = `${input.id}-preview`;
      let previewContainer = document.getElementById(previewId);
      
      if (!previewContainer) {
          previewContainer = document.createElement('div');
          previewContainer.id = previewId;
          previewContainer.className = 'mt-2 hidden';
          input.parentNode.insertBefore(previewContainer, input.nextSibling);
      }
      
      input.addEventListener('change', function() {
          previewContainer.innerHTML = '';
          previewContainer.classList.add('hidden');
          
          if (this.files && this.files[0]) {
              const reader = new FileReader();
              
              reader.onload = function(e) {
                  const img = document.createElement('img');
                  img.src = e.target.result;
                  img.className = 'h-16 border rounded';
                  
                  const label = document.createElement('p');
                  label.textContent = 'Preview:';
                  label.className = 'text-xs text-gray-500 mb-1';
                  
                  previewContainer.appendChild(label);
                  previewContainer.appendChild(img);
                  previewContainer.classList.remove('hidden');
              }
              
              reader.readAsDataURL(this.files[0]);
          }
      });
  });
});
