<div id="container-header" class="card-header text-center">
    <ul class="nav nav-pills">
        <li class="nav-item">
            <a class="nav-link {{ ($active ?? '') === 'dashboard' ? 'active' : '' }}" href="index.php">Dashboard</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ ($active ?? '') === 'validacao' ? 'active' : '' }}" href="validacao">Validação</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ ($active ?? '') === 'tutorial' ? 'active' : '' }}" href="tutorial">Tutorial</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ ($active ?? '') === 'outorga' ? 'active' : '' }}" href="outorga">Outorga</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ ($active ?? '') === 'businessintelligence' ? 'active' : '' }}" href="businessintelligence">B.I.</a>
        </li>
    </ul>
</div>
