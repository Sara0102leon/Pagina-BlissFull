<?php 
if(!isset($_SESSION["user_id"])){ Core::redir("./");}
$user= UserData::getById($_SESSION["user_id"]);
if($user==null){ Core::redir("./");}

if(isset($_GET["opt"]) && $_GET["opt"]=="all"):?>
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title">Lista de Categorías</h2>
      </div>
      <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">
          <a href="./?view=categories&opt=new" class="btn btn-primary">
            <i class='bi bi-plus'></i> Nueva Categoría
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="page-body">
  <div class="container-xl">
    <div class="card">
      <div class="card-status-top bg-primary"></div>
      <div class="card-header">
        <h3 class="card-title">Categorías</h3>
      </div>
      <div class="card-body">
    <?php
    $categories = CategoryData::getActives();
    if(count($categories)>0):?>
      <div class="table-responsive">
        <table class="table card-table table-vcenter text-nowrap datatable">
          <thead>
            <tr>
              <th class="w-1">Orden</th>
              <th>Imagen</th>
              <th>Nombre</th>
              <th>Activo</th>
              <th class="w-1"></th>
            </tr>
          </thead>
          <tbody>
          <?php foreach($categories as $cat):?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-1">
                  <span class="text-muted me-1"><?php echo (int)$cat->position; ?></span>
                  <a href="./?action=categories&opt=up&id=<?php echo $cat->id;?>" class="btn btn-outline-secondary btn-sm" title="Subir"><i class="bi-chevron-up"></i></a>
                  <a href="./?action=categories&opt=down&id=<?php echo $cat->id;?>" class="btn btn-outline-secondary btn-sm" title="Bajar"><i class="bi-chevron-down"></i></a>
                </div>
              </td>
              <td>
                <?php if($cat->image!="" && file_exists("storage/categories/".$cat->image)):?>
                  <img src="storage/categories/<?php echo htmlspecialchars($cat->image); ?>" alt="" style="width:36px;height:36px;border-radius:8px;object-fit:cover;" class="border">
                <?php else: ?>
                  <span class="text-muted"><i class="bi bi-image"></i></span>
                <?php endif; ?>
              </td>
              <td><?php echo htmlspecialchars($cat->name); ?></td>
              <td>
                <?php if($cat->is_active):?><i class="bi bi-check-lg text-success"></i><?php else: ?><i class="bi bi-x-lg text-danger"></i><?php endif; ?>
              </td>
              <td>
                <div class="btn-list flex-nowrap">
                  <a href="./?view=categories&opt=edit&id=<?php echo $cat->id;?>" class="btn btn-warning btn-sm"><i class="bi-pencil"></i></a>
                  <a href="./?action=categories&opt=del&id=<?php echo $cat->id;?>" class="btn btn-danger btn-sm"><i class="bi-trash"></i></a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        </div>
    <?php else:?>
        <p class="alert alert-warning mb-0">No hay categorías.</p>
    <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php elseif(isset($_GET["opt"]) && $_GET["opt"]=="new"):?>
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title">Agregar Categoría</h2>
      </div>
    </div>
  </div>
</div>
<div class="page-body">
  <div class="container-xl">
    <div class="card">
      <div class="card-status-top bg-success"></div>
      <div class="card-header">
        <h3 class="card-title">Nueva Categoría</h3>
      </div>
      <div class="card-body">
        <form method="post" enctype="multipart/form-data" action="./?action=categories&opt=add">
          <div class="mb-3">
            <label class="form-label">Nombre*</label>
            <input type="text" name="name" class="form-control" required placeholder="Nombre">
          </div>
          <input type="hidden" name="short_name" value="">

          <div class="mb-3">
            <label class="form-label">Imagen de la categoría <span class="text-muted small">(opcional)</span></label>
            <input type="file" name="image" class="form-control" accept="image/*">
            <div class="text-muted small mt-1">Se muestra en el menú público (chips de categoría y título del grid).</div>
          </div>

          <div class="mb-3">
            <label class="form-check">
              <input class="form-check-input" type="checkbox" name="is_active" checked>
              <span class="form-check-label">Categoria Activa</span>
            </label>
          </div>
          <div class="form-footer mt-4">
            <button type="submit" class="btn btn-primary w-100">Agregar Categoría</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php elseif(isset($_GET["opt"]) && $_GET["opt"]=="edit"):?>
<?php $cat = CategoryData::getById($_GET["id"]);?>
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title">Editar Categoría</h2>
      </div>
    </div>
  </div>
</div>
<div class="page-body">
  <div class="container-xl">
    <div class="card">
      <div class="card-status-top bg-info"></div>
      <div class="card-header">
        <h3 class="card-title">Editar Categoría</h3>
      </div>
      <div class="card-body">
        <form method="post" enctype="multipart/form-data" action="./?action=categories&opt=upd">
          <input type="hidden" name="id" value="<?php echo $cat->id;?>">
          <div class="mb-3">
            <label class="form-label">Nombre*</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($cat->name);?>" class="form-control" required placeholder="Nombre">
          </div>
          <input type="hidden" name="short_name" value="">

          <div class="mb-3">
            <label class="form-label">Imagen de la categoría <span class="text-muted small">(opcional)</span></label>
            <?php if($cat->image!="" && file_exists("storage/categories/".$cat->image)):?>
              <div class="mb-2 d-flex align-items-center gap-3">
                <img src="storage/categories/<?php echo htmlspecialchars($cat->image); ?>" alt="" style="width:52px;height:52px;border-radius:10px;object-fit:cover;" class="border">
                <label class="form-check mb-0">
                  <input class="form-check-input" type="checkbox" name="remove_image" value="1">
                  <span class="form-check-label">Quitar imagen</span>
                </label>
              </div>
            <?php endif; ?>
            <input type="file" name="image" class="form-control" accept="image/*">
            <div class="text-muted small mt-1">Se muestra en el menú público (chips de categoría y título del grid).</div>
          </div>

          <div class="mb-3">
            <label class="form-check">
              <input class="form-check-input" type="checkbox" name="is_active" <?php if($cat->is_active){ echo "checked"; }?>>
              <span class="form-check-label">Categoria Activa</span>
            </label>
          </div>
          <div class="form-footer mt-4">
            <button type="submit" class="btn btn-success w-100">Actualizar Categoría</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
