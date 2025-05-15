<style>
    body {
        background-image: url('/campus_ems/assets/images/events-bg.jpg');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        background-repeat: no-repeat;
        min-height: 100vh;
        color: #fff;
    }

    .bg-image-wrapper {
        position: relative;
        min-height: 15vh;
    }

    .bg-image-wrapper::before {
        content: "";
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: linear-gradient(rgba(30, 30, 30, 0.75), rgba(30, 30, 30, 0.75)), url('assets/images/events-bg.jpg') center center / cover no-repeat fixed;
        z-index: -1;
        pointer-events: none;
    }
</style>
</head>

<body>

    <div class="bg-image-wrapper">
        <!-- Your home page content here -->
    </div>

</body>