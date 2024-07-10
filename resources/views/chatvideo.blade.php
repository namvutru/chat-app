<head>
    <title>WebRTC App</title>
</head>

<body>
    <video id="localVideo" autoplay></video>
    <video id="remoteVideo" autoplay></video>
    <button id="startButton">Start Call</button>
    <button id="hangupButton">Hang Up</button>
</body>

<script>
    let localStream
    let remoteStream
    let localPeerConnection
    let remotePeerConnection

    // Get references to HTML elements
    const startButton = document.getElementById('startButton')
    const hangupButton = document.getElementById('hangupButton')
    const localVideo = document.getElementById('localVideo')
    const remoteVideo = document.getElementById('remoteVideo')

    // Add event listeners
    startButton.addEventListener('click', startCall)
    hangupButton.addEventListener('click', hangupCall)

    // Create the offer
    function startCall() {
        startButton.disabled = true
        hangupButton.disabled = false

        // Get local media stream
        navigator.mediaDevices
            .getUserMedia({
                video: true,
                audio: true
            })
            .then((stream) => {
                localStream = stream
                localVideo.srcObject = stream

                // Create local peer connection
                localPeerConnection = new RTCPeerConnection()

                // Add local stream to connection
                localStream.getTracks().forEach((track) => {
                    localPeerConnection.addTrack(track, localStream)
                })

                // Create offer
                localPeerConnection
                    .createOffer()
                    .then((offer) => {
                        localPeerConnection.setLocalDescription(offer)

                        // Send offer to signaling server
                        signal('offer', offer)
                    })
                    .catch((error) => {
                        console.error('Error creating offer:', error)
                    })

                // Set up remote peer connection
                remotePeerConnection = new RTCPeerConnection()

                // Add remote stream to connection
                remotePeerConnection.ontrack = (event) => {
                    remoteStream = event.streams[0]
                    remoteVideo.srcObject = remoteStream
                }

                // Handle ice candidates for local peer connection
                localPeerConnection.onicecandidate = (event) => {
                    if (event.candidate) {
                        // Send ice candidate to signaling server
                        signal('candidate', event.candidate)
                    }
                }

                // Handle incoming ice candidates for remote peer connection
                fromSignal.on('ice-candidate', (candidate) => {
                    remotePeerConnection.addIceCandidate(candidate)
                })

                // Handle incoming offer from remote peer
                fromSignal.on('offer', (offer) => {
                    remotePeerConnection.setRemoteDescription(offer)

                    // Create answer
                    remotePeerConnection
                        .createAnswer()
                        .then((answer) => {
                            remotePeerConnection.setLocalDescription(answer)

                            // Send answer to signaling server
                            signal('answer', answer)
                        })
                        .catch((error) => {
                            console.error('Error creating answer:', error)
                        })
                })

                // Handle incoming answer from remote peer
                fromSignal.on('answer', (answer) => {
                    localPeerConnection.setRemoteDescription(answer)
                })
            })
            .catch((error) => {
                console.error('Error accessing media devices:', error)
            })
    }

    // Hang up the call
    function hangupCall() {
        localPeerConnection.close()
        remotePeerConnection.close()
        localPeerConnection = null
        remotePeerConnection = null
        startButton.disabled = false
        hangupButton.disabled = true
        localVideo.srcObject = null
        remoteVideo.srcObject = null

        // Send hang up signal to signaling server
        signal('end', 'hang-up')
    }

    // Function for sending data to signaling server
    function signal(eventName, data) {
        // Your implementation for sending data to the signaling server
    }

    // Event listener for other incoming data from signaling server
    fromSignal.on('data', (data) => {
        // Your implementation for handling incoming data from the signaling server
    })
</script>