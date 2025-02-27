
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        @import "bourbon";

body {
	background: #eee !important;	
}

.wrapper {	
	margin-top: 80px;
  margin-bottom: 80px;
}

.form-signin {
  max-width: 380px;
  padding: 15px 35px 45px;
  margin: 0 auto;
  background-color: #fff;
  border: 1px solid rgba(0,0,0,0.1);  

  .form-signin-heading {
    text-align: center;
	  margin-bott om: 30px;
	}

	.checkbox {
	  font-weight: normal;
	}

	.form-control {
	  position: relative;
	  font-size: 16px;
	  height: auto;
	  padding: 10px;
    margin-bottom: 30px;
		@include box-sizing(border-box);

		&:focus {
		  z-index: 2;
		}
	}

	input[type="text"] {
	  margin-bottom: -1px;
	  border-bottom-left-radius: 0;
	  border-bottom-right-radius: 0;
	}

	input[type="password"] {
	  margin-bottom: 20px;
	  border-top-left-radius: 0;
	  border-top-right-radius: 0;
	}
}

    </style>


  </head>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('logout') === 'success') {
        Swal.fire({
            title: "Logged Out!",
            text: "You have logged out successfully.",
            icon: "success",
            confirmButtonText: "OK"
        });
    }
});
</script>

  <body>
    <div class="wrapper">
        <form class="form-signin" action="login.php" method="POST">
          <h2 class="form-signin-heading">Login</h2>
          <input type="text" class="form-control" name="email" placeholder="Email Address" required="" autofocus="" />
          <input type="password" class="form-control" name="password" placeholder="Password" required=""/>      
          <button class="btn btn-lg btn-primary btn-block" type="submit" name="login">Login</button>
          <br>
          <a href="forgot_pass.html" style="text-decoration: none;">Forgot Password?</a> 
          <br>
          <br>
          <a href="index.php" style="text-decoration: none;" class="btn btn-secondary">Back</a>  
        </form>
      </div>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  

      <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function () {
        $(".form-signin").submit(function (e) {
            e.preventDefault();

            var email = $("input[name='email']").val();
            var password = $("input[name='password']").val();

            $.ajax({
                url: "login-form.php",
                type: "POST",
                data: { email: email, password: password },
                dataType: "json",
                success: function (response) {
                    if (response.status === "success") {
                        Swal.fire({
                            icon: "success",
                            title: "Login Successful!",
                            text: "Redirecting to dashboard...",
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            if (response.user_type === "admin") {
                                window.location.href = "dashboard.php";
                            } else if (response.user_type === "employee") {
                                window.location.href = "dashboard.php";
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Login Failed",
                            text: response.message
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Something went wrong!"
                    });
                }
            });
        });
    });
    </script>

    </body>
</html>
