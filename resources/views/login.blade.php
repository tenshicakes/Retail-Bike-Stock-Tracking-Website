<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Bike Shop Inventory</title>

    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-light">

    <div class="container-fluid min-vh-100 d-flex flex-column p-0">

        <div class="row g-0 flex-grow-1">
            
            <div class="col-lg-6 bg-white d-flex flex-column align-items-center justify-content-center p-4 p-md-5 text-center">
                <img src="{{ asset('images/JFM.png') }}" alt="Logo" class="logo mb-4">  
                <h1 class="display-5 display-md-4 fw-bold text-dark">Alvin's Bike Repair Shop</h1>
                <p class="text-muted mb-0 fs-6">Retail Bike Stock Tracking System</p>
            </div>

            <div class="col-lg-6 login-right-side d-flex align-items-center justify-content-center p-3 p-sm-4 p-md-5">
                
                <div class="glass-login-box p-4 p-sm-5 shadow-lg my-auto">
                    <h3 class="text-white text-center mb-4 fw-bold fs-3 fs-sm-3">Login your account</h3>
                    
                    <form method="POST" action="/login">
                        @csrf <div class="mb-3">
                            <label for="username" class="form-label text-white fw-semibold fs-4">Username</label>
                            <input type="text" class="form-control bg-white text-dark py-2" id="username" name="username" required autofocus>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label text-white fw-semibold fs-4">Password</label>
                            <input type="password" class="form-control bg-white text-dark py-2" id="password" name="password" required>
                        </div>
                        
                        <div class="d-grid mt-4 button-container">
                            <button type="submit" class="btn btn-info fw-bold text-white py-2 shadow-sm fs-4">
                                Login
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</body>
</html>

