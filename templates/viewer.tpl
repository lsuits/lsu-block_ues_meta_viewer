
<form method="GET" class="search-form">
    <div class="search-table">
        {print table=$search}
    </div>
    <div class="search-buttons center">
        <input type="submit" name="search"
               value="{"search:moodle"|s}"/>
    </div>
</form>

<div class="results">
    {if $posted}
        {if empty($result)}
            <div class="no-results center padded">
                {"no_results"|s}
            </div>
        {else}
            <div class="count-results center padded">
                {"found_results"|s} {$count}
            </div>
            <div class="results-table margin-center">
                {print table=$result}
            </div>
        {/if}
    {/if}
</div>
