'use strict';
window.addEventListener('load', init());

function init() {
    var videoWidth = (window.innerWidth)/1.03;
    var videoHeight = (window.innerWidth)/1.4;

    var c = document.getElementById("can");
    c.width = videoWidth;
    c.height = videoHeight;
    var context = c.getContext('2d');

    function animate() {
        if (context) {
            var piImage = new Image();

            piImage.onload = function () {
                context.drawImage(piImage, 0, 0, c.width, c.height);
            }

            piImage.src = "http://malinovky5.ssinfotech.cz/cam_pic.php?time=" + new Date().getTime();
        }

        requestAnimationFrame(animate);
    }
    var video = requestAnimationFrame(animate);
}