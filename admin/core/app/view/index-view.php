<?php
if(isset($_SESSION["user_id"])){
  Core::redir("./?view=home");
}
?>
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title">
          Dashboard
        </h2>
      </div>
    </div>
  </div>
</div>
<div class="page-body">
  <div class="container-xl">
    <div class="card card-md mb-3">
      <div class="card-stamp card-stamp-lg">
        <div class="card-stamp-icon bg-primary">
          <i class="bi bi-star" style="font-size: 3rem;"></i>
        </div>
      </div>
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-10">
            <h3 class="h1">Bienvenido al panel</h3>
            <div class="markdown text-muted">sazón: administra tu menú, tus pedidos y tu equipo desde aquí.</div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="row row-cards">
      <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-auto">
                <span class="bg-primary text-white avatar">
                  <i class="bi bi-eye"></i>
                </span>
              </div>
              <div class="col">
                <div class="font-weight-medium">Vistas</div>
                <div class="text-muted">Gestiona las órdenes de tu tienda.</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-auto">
                <span class="bg-green text-white avatar">
                  <i class="bi bi-bookmark"></i>
                </span>
              </div>
              <div class="col">
                <div class="font-weight-medium">Acciones</div>
                <div class="text-muted">Mantén tu carta y tus productos al día.</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-auto">
                <span class="bg-twitter text-white avatar">
                  <i class="bi bi-table"></i>
                </span>
              </div>
              <div class="col">
                <div class="font-weight-medium">Modelos</div>
                <div class="text-muted">Tu equipo, con roles por tienda.</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-auto">
                <span class="bg-facebook text-white avatar">
                  <i class="bi bi-plus"></i>
                </span>
              </div>
              <div class="col">
                <div class="font-weight-medium">Mucho mas</div>
                <div class="text-muted">Tu menú digital, en tu propio subdominio.</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>