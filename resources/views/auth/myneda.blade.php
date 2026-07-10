<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="/assets/css/myneda.css?v=1.0.3" />
<title>DEPDev - eBudget</title>
</head>
<body>
  <div class="container">
    <div class="left-panel" style="background-image: url('{{ asset("/assets/img/neda/background_with_blue_shade.jpg") }}');">
      <div class="company-info">
        {{-- <h4>NCSOIS</h4> --}}
      </div>
      <div class="centered-info">
        <h4>Welcome to</h4>
        <h3>DEPARTMENT OF ECONOMY, PLANNING. AND DEVELOPMENT</h3>
        <div class="hr"> </div>
        <p>myDEPDev Portal</p>
      </div>
      <div class="bottom-info">
        <small>&copy; 2023 by Information and Communications Technology Staff, ICTS</small>
      </div>
    </div>

    <div class="right-panel">
      <div class="login-form">
        <img src="/assets/img/neda/logo.png" alt="Logo">
        <h2> myDEPDev Portal</h2>
        {{-- <form action="index.php" method="POST"> --}}
        <form role="form" method="POST" action="{{ route('login.myneda') }}" class="text-start">
          @csrf
          <input type="text" value="{{ old('username_ncsois') }}" name="username_ncsois" placeholder="Enter username or email" required>
          <input type="password" value="{{ old('password_ncsois') }}" name="password_ncsois" placeholder="Enter password" required>
          <button type="submit"><b>LOG IN</b></button>
        </form>
        {{-- <a href="forgot_password.php">Forgot Password?</a> --}}
      </div>
    </div>

    <div id="myModal" class="modal">
      <div class="modal-content">
        <span class="close">&times;</span>
        @if($errors->has('message'))
          <p>{{ $errors->first() }}</p>
        @endif
      </div>
    </div>

  </div>















  <script>
    @if($errors->has('message'))
      window.onload = function () {
        modal.style.display = "block";
      }
    @endif

    // Get the modal
    var modal = document.getElementById("myModal");

    // Get the button that opens the modal
    var btn = document.getElementById("myBtn");

    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close")[0];

    // When the user clicks the button, open the modal
    // btn.onclick = function() {
    //   modal.style.display = "block";
    // }

    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
      modal.style.display = "none";
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
      if (event.target == modal) {
        modal.style.display = "none";
      }
    }
  </script>
</body>
</html>
