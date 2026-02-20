import { useState, useEffect } from "react";
import type { PaginationInfo } from "../types";

interface GalleryHeaderProps {
  onPerPageChange: (perPage: number) => void;
  initialPerPage?: number;
  pagination?: PaginationInfo | null;
  currentPage: number;
  onPageChange: (page: number) => void;
}

export function GalleryHeader({
  onPerPageChange,
  initialPerPage = 20,
  pagination,
  currentPage,
  onPageChange,
}: GalleryHeaderProps) {
  const [selectedPerPage, setSelectedPerPage] = useState(initialPerPage);
  const [jumpPage, setJumpPage] = useState<number>(currentPage);

  useEffect(() => {
    setJumpPage(currentPage);
  }, [currentPage]);

  // Update selectedPerPage when initialPerPage prop changes
  useEffect(() => {
    setSelectedPerPage(initialPerPage);
  }, [initialPerPage]);

  const handlePerPageChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    const newPerPage = Number(e.target.value);
    setSelectedPerPage(newPerPage);
    onPerPageChange(newPerPage);
  };

  const handleJumpToPage = (e: React.ChangeEvent<HTMLSelectElement>) => {
    const page = Number(e.target.value);
    setJumpPage(page);

    if (pagination && page >= 1 && page <= pagination.last_page) {
      onPageChange(page);
    }
  };

  const goToPreviousPage = () => {
    if (currentPage > 1) {
      onPageChange(currentPage - 1);
    }
  };

  const goToNextPage = () => {
    if (pagination && currentPage < pagination.last_page) {
      onPageChange(currentPage + 1);
    }
  };

  return (
    <header className="flex flex-col sm:flex-row sm:justify-between sm:items-center px-6 py-4 bg-gray-800 border-b border-gray-600 gap-4">
      <div className="flex items-center gap-2">
        <div className="px-4 mt-2">
          Photos per page:
          <select
            value={selectedPerPage}
            onChange={handlePerPageChange}
            className="ml-2 px-3 py-1 border border-gray-300 rounded-md bg-white text-gray-700 text-sm"
            aria-label="Number of photos per page"
          >
            <option value="20">20</option>
            <option value="50">50</option>
            <option value="100">100</option>
            <option value="500">500</option>
          </select>
        </div>
      </div>

      <div className="flex flex-wrap items-center justify-center sm:justify-end gap-4">
        {pagination && pagination.last_page > 1 && (
          <>
            <button
              onClick={goToPreviousPage}
              disabled={currentPage <= 1}
              className="px-3 py-2 rounded-md text-sm font-medium disabled:bg-gray-700 disabled:cursor-not-allowed transition-colors"
              aria-label="Previous page"
            >
              Previous
            </button>

            <span className="text-sm font-medium text-gray-200 whitespace-nowrap">
              Page {pagination.current_page} of {pagination.last_page}
            </span>

            <button
              onClick={goToNextPage}
              disabled={currentPage >= pagination.last_page}
              className="px-3 py-2 rounded-md text-sm font-medium disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
              aria-label="Next page"
            >
              Next
            </button>

            <select
              name="jump-to-page"
              value={jumpPage}
              onChange={handleJumpToPage}
              className="px-3 py-2 border border-gray-700 rounded-md bg-white text-gray-700 text-sm min-w-[80px]"
              aria-label="Jump to page"
            >
              {Array.from(
                { length: pagination.last_page },
                (_, i) => i + 1,
              ).map((pageNum) => (
                <option key={pageNum} value={pageNum}>
                  {pageNum}
                </option>
              ))}
            </select>
          </>
        )}
      </div>
    </header>
  );
}
