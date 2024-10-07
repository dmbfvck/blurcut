<?php

$this->title = 'BlurCut';

$this->registerCss("
    video, canvas {
        width: 100%;
        height: auto;
    }
    .modal-custom {
        max-width: 500px; /* Adjust width as needed */
        margin: 1.75rem auto; /* Center the modal vertically */
    }
");
?>

<div class="site-index">
    <div class="row bg-primary">
        <div class="jumbotron text-center mt-5 mb-5">
            <h1 class="display-4 text-white">Welcome to BlurCut Live Streaming</h1>
            <p class="lead text-white">Auto-blur unwanted faces while live streaming!</p>
            <p><button class="btn btn-lg btn-success" id="start-streaming">Start Streaming</button></p>
        </div>
    </div>

    <section class="container py-5">
        <div class="text-center mb-4">
            <h2>How it Works</h2>
        </div>

        <div class="row">
            <div class="col-lg-4 mb-4 text-center">
                <img src="<?= Yii::$app->request->baseUrl ?>/icon/icon.png" alt="Detect Faces Icon" style="width: 75px; height: auto;">
                <h2>Detect Faces</h2>
                <p>Our AI-powered tool detects faces in real-time during your live streams.</p>
            </div>

            <div class="col-lg-4 mb-4 text-center">
                <img src="<?= Yii::$app->request->baseUrl ?>/icon/blurr.png" alt="Blur Faces Icon" style="width: 75px; height: auto;">
                <h2>Blur Faces</h2>
                <p>Automatically blur unwanted faces to protect privacy.</p>
            </div>

            <div class="col-lg-4 mb-4 text-center">
                <img src="<?= Yii::$app->request->baseUrl ?>/icon/live.png" alt="Live Stream Icon" style="width: 75px; height: auto;">
                <h2>Live Stream</h2>
                <p>Continue live streaming while protecting the privacy of others.</p>
            </div>
        </div>
    </section>


    <div class="modal fade" id="videoModal" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-custom">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="videoModalLabel">Live Streaming</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <video id="videoElement" autoplay playsinline></video>
                    <canvas id="canvasElement"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<?php

$this->registerJs(
    <<<JS
$(document).ready(async function() {
    let videoStream; 

    // Load BodyPix model for background segmentation
    async function loadBodyPixModel() {
        return await bodyPix.load();
    }

    // Load Face API models for face detection
    async function loadFaceApiModels() {
        try {
            await faceapi.nets.tinyFaceDetector.loadFromUri('/models');
            await faceapi.nets.faceLandmark68Net.loadFromUri('/models');
            await faceapi.nets.faceRecognitionNet.loadFromUri('/models');
            console.log('Face API models loaded successfully.');
        } catch (error) {
            console.error('Error loading Face API models:', error);
        }
    }

    async function startVideoStreaming() {
        const videoElement = document.getElementById('videoElement');
        const canvasElement = document.getElementById('canvasElement');
        const ctx = canvasElement.getContext('2d');

        try {
            videoStream = await navigator.mediaDevices.getUserMedia({ video: true });
            videoElement.srcObject = videoStream;

            const bodyPixNet = await loadBodyPixModel();  // Load BodyPix for background blur
            await loadFaceApiModels();                   // Load Face API for face detection

            videoElement.addEventListener('play', () => {
                function processFrame() {
                    console.log('Processing frame...');
                    bodyPixNet.segmentPerson(videoElement, { flipHorizontal: false })
                        .then(segmentation => {
                            const backgroundBlurAmount = 20;
                            const edgeBlurAmount = 5;
                            
                            bodyPix.drawBokehEffect(
                                canvasElement, videoElement, segmentation, backgroundBlurAmount, edgeBlurAmount, true
                            );

                            faceapi.detectAllFaces(videoElement, new faceapi.TinyFaceDetectorOptions())
                                .then(detections => {
                                    console.log('Detected faces:', detections);
                                    faceapi.draw.drawDetections(canvasElement, detections);
                                });

                            requestAnimationFrame(processFrame);
                        });
                }

                processFrame();
            });
        } catch (error) {
            console.error('Error accessing media devices.', error);
        }
    }

    function stopVideoStreaming() {
        if (videoStream) {
            videoStream.getTracks().forEach(track => track.stop());
            videoStream = null;
        }
    }

    document.getElementById('start-streaming').addEventListener('click', () => {
        $('#videoModal').modal('show');
        startVideoStreaming();
    });

    $('#videoModal').on('hidden.bs.modal', () => {
        stopVideoStreaming();
    });
});
JS
);

?>