export const preloadImages = (imageUrls: string[], callback: () => any) => {
    var loadedImages = 0;
    var totalImages = imageUrls.length;

    // Function to call when each image is loaded
    function imageLoaded() {
        loadedImages++;
        if (loadedImages === totalImages) {
            // All images are loaded
            callback();
        }
    }

    // Loop through each image URL and preload it
    imageUrls.forEach(function (url) {
        var img = new Image();
        img.onload = imageLoaded;
        img.onerror = imageLoaded;
        img.src = url;
    });
};
