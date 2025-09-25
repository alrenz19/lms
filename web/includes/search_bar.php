<form class="mb-4">
    <div class="input-group">
        <input type="text" 
               class="form-control" 
               placeholder="<?php echo $search_placeholder ?? 'Search...'; ?>" 
               name="search" 
               value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
        <button class="btn btn-primary" type="submit">
            <i class="bi bi-search"></i>
        </button>
    </div>
</form>
