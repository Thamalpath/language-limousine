<aside class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-header">
      <div class="logo-icon">
        <img src="assets/images/logo-icon.png" class="logo-img" alt="">
      </div>
      <div class="logo-name flex-grow-1">
        <h5 class="mb-0">Buddy Hotel</h5>
      </div>
      <div class="sidebar-close">
        <span class="material-icons-outlined">close</span>
      </div>
    </div>
    <div class="sidebar-nav">
        <!--navigation-->
        <ul class="metismenu" id="sidenav">
          <li>
            <a href="dashboard">
              <div class="parent-icon"><i class="material-icons-outlined">home</i>
              </div>
              <div class="menu-title">Dashboard</div>
            </a>
          </li>          
          <li>
            <a href="reservation">
              <div class="parent-icon"><i class="material-icons-outlined">book_online</i>
              </div>
              <div class="menu-title">Reservation</div>
            </a>
          </li>
          <li>
            <a href="registration">
              <div class="parent-icon"><i class="material-icons-outlined">how_to_reg</i>
              </div>
              <div class="menu-title">Registration</div>
            </a>
          </li>          
          <li>
            <a href="category">
              <div class="parent-icon"><i class="material-icons-outlined">widgets</i>
              </div>
              <div class="menu-title">Categories</div>
            </a>
          </li>
          <li>
            <a href="billing">
              <div class="parent-icon"><i class="material-icons-outlined">receipt_long</i>
            </div>
            <div class="menu-title">Billing</div>
            </a>
          </li>          
          <li>
            <a href="rooms">
              <div class="parent-icon"><i class="material-icons-outlined">bed</i>
              </div>
              <div class="menu-title">Rooms</div>
            </a>
          </li>
          <li>
            <a href="items">
              <div class="parent-icon"><i class="material-icons-outlined">restaurant</i>
              </div>
              <div class="menu-title">Items</div>
            </a>
          </li>
          <li>
            <a href="#" data-bs-toggle="modal" data-bs-target="#rateModal">
                <div class="parent-icon"><i class="material-icons-outlined">paid</i></div>
                <div class="menu-title">Rate</div>
            </a>
          </li>
        </ul>

         <!--end navigation-->
        <ul class="metismenu" id="sidenav" style="position: absolute; bottom: 10px; width: 100%;">
        <li>
            <a href="profile">
              <div class="parent-icon"><i class="material-icons-outlined">account_circle</i>
              </div>
              <div class="menu-title">Profile</div>
            </a>
          </li>
          <li>
            <a href="logout">
              <div class="parent-icon"><i class="material-icons-outlined">logout</i>
              </div>
              <div class="menu-title">Logout</div>
            </a>
          </li>
        </ul>
        <!--end navigation-->
    </div>
  </aside>

  <!-- Bootstrap Modal -->
  <div class="modal fade" id="rateModal" tabindex="-1" aria-labelledby="rateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-bottom-0 py-2 bg-grd-deep-blue">
          <h5 class="modal-title fw-bold">Enter Password</h5>
          <a href="javascript:;" class="primaery-menu-close" data-bs-dismiss="modal">
            <i class="material-icons-outlined">close</i>
          </a>
        </div>
        <div class="modal-body mt-3 mb-3">
          <input type="password" id="ratePassword" class="form-control" placeholder="Password">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-grd-deep-blue px-4 fw-bold text-white" onclick="checkPassword()">Submit</button>
        </div>
      </div>
    </div>
  </div>

  <script>
      function checkPassword() {
          const hardcodedPassword = "Rate@123";  // Replace with your hardcoded password
          const inputPassword = document.getElementById('ratePassword').value;

          if (inputPassword === hardcodedPassword) {
              window.location.href = 'rate';  // Redirect to the rate page if password is correct
          } else {
              notyf.error('Incorrect password. Please try again.');
          }
      }
  </script>
