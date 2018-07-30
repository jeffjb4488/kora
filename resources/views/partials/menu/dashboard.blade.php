<li class="navigation-item">
    <a href="#" class="menu-toggle navigation-toggle-js">
      <span>Dashboard</span>
      <i class="icon icon-chevron"></i>
    </a>
    <ul class="navigation-sub-menu navigation-sub-menu-js">
        <li class="link link-head">
            <a href="{{ url('/dashboard') }}">
              <i class="icon icon-dashboard"></i>
              <span>Dashboard</span>
            </a>
        </li>
        <li class="spacer full"></li>
        <li class="link">
            <a href="#">Edit Dashboard</a>
        </li>
        <li class="link">
            <a href="#">Add Dashboard Block</a>
        </li>
    </ul>
</li>

<li class="navigation-item">
    <a href="#" class="menu-toggle navigation-toggle-js">
      <span> Projects</span>
      <i class="icon icon-chevron"></i>
    </a>
    <ul class="navigation-sub-menu navigation-sub-menu-js">
        <li class="link link-head">
          <a href="{{ url('/projects') }}">
            <i class="icon icon-projects"></i>
            <span>Projects</span>
          </a>
        </li>

		<li class="spacer full"></li>
        @if(\Auth::user()->admin==1)
          <li class="link first">
            <a href="{{ url('/projects/create') }}">Create New Project</a>
          </li>
          <li class="link">
            <a href="{{ url('/projects/import') }}">Import Project Setup</a>
          </li>
        @endif

        <?php $allowed_projects = \Auth::user()->allowedProjects() ?>
        @if(sizeof($allowed_projects) > 1)
            <li class="link">
              <a href='#' class="navigation-sub-menu-toggle navigation-sub-menu-toggle-js">
                <span>Jump to Project</span>
                <i class="icon sub-menu-icon icon-plus"></i>
              </a>
			  
			  <?php
			  // Sort projects by name
			  $name_pid_projects = [];
			  
			  foreach ($allowed_projects as $project)
			  {
			    $name_pid_projects[$project->pid] = $project->name;
			  }
			  
			  asort($name_pid_projects, SORT_NATURAL | SORT_FLAG_CASE);
			  ?>
			  
              <ul class="navigation-deep-menu navigation-deep-menu-js">
                @foreach($name_pid_projects as $project_pid => $project_name)
                  <li class="deep-menu-item">
                    <a href="{{ url('/projects/'.$project_pid) }}">{{ $project_name }}</a>
                  </li>
                @endforeach
              </ul>
            </li>
        @endif
		
		@if(\Auth::user()->admin==0)
		  <li class="link">
            <a class="nav-textwrap-override project-request-perms-js" href="#">Request Project Permissions</a>
          </li>
		@endif
    </ul>
</li>
