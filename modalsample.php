<style>
    .modal-content {
        background: rgba(43, 45, 66, 0.7) !important;
        backdrop-filter: blur(10px) !important;
    }
</style>

<!-- Cancel Event Modal -->
<div class="modal fade" tabindex="-1" role="dialog" id="cancelEventModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-3 shadow">
            <div class="modal-body p-4 text-center text-white">
                <h5 class="mb-2">Cancel Event</h5>
                <p class="mb-0">Are you sure you want to cancel this event?</p>
            </div>
            <div class="modal-footer flex-nowrap p-0">
                <button type="button"
                    class="btn btn-lg btn-link fs-6 text-decoration-none text-danger col-6 py-3 m-0 rounded-0 border-end"
                    id="confirmCancelBtn"><strong>Yes, cancel</strong></button>
                <button type="button" class="btn btn-lg btn-link fs-6 text-decoration-none col-6 py-3 m-0 rounded-0"
                    data-bs-dismiss="modal">No thanks</button>
            </div>
        </div>
    </div>
</div>