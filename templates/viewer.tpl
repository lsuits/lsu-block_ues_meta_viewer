
<form method="POST" class="search-form">
    <div class="search-table">
        {print table=$search}
    </div>
    <div class="search-buttons">
        <input type="submit" name="search"
               value="{"search:moodle"|s}"/>
    </div>
</form>

<div class="results">
    {if $posted}
        {if empty($result)}
            <div class="no-results">
                {"no_results"|s}
            </div>
        {else}
            <div class="count-results">
                {"found_results"|s} {$count}
            </div>
            <div class="results-table">
                {print table=$result}
            </div>
        {/if}
    {/if}
</div>
