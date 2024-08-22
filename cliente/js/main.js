document.getElementById('fileUpload').addEventListener('change', function(e) {
    const reader = new FileReader();
    reader.onload = function() {
        document.getElementById('profileImage').src = reader.result;
    }
    reader.readAsDataURL(e.target.files[0]);
});