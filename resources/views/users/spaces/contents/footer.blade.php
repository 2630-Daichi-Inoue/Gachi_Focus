<div class="card-footer border-bottom border-start border-end border-dark bg-grey">
    <div class="col">
        @forelse ($space->categorySpace as $category_space)
            <div class="badge text-dark fw-light border border-dark" style="background-color: #C9DFEC">
                {{ $category_space->category->name }}
            </div>
        @empty
            <!-- <div class="badge bg-dark text-wrap">Uncategorized</div> -->
        @endforelse
    </div>
</div>