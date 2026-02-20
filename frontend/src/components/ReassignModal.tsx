import { useState } from "react";
import type { Photo } from "../types";
import YearSelect from "./YearSelect";
import { normalizeYear } from "../utils/date";

interface Props {
  photo: Photo;
  onConfirm: (data: { accession: string; year: string }) => void;
  onCancel: () => void;
  error: string | null;
  loading: boolean;
}

export default function ReassignModal({
  photo,
  onConfirm,
  onCancel,
  error,
  loading,
}: Props) {
  const [accession, setAccession] = useState("");
  const [year, setYear] = useState(() => normalizeYear(photo.year).toString());

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onConfirm({ accession: accession.trim(), year: year.trim() });
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60">
      <div className="bg-gray-900 border border-gray-700 rounded-md shadow-lg max-w-md w-full mx-4 p-6">
        <h2 className="text-lg font-semibold mb-4">
          Reassign accession number
        </h2>
        <p className="mb-4 text-sm text-gray-300">
          Current accession: {photo.year}
          {photo.suffix}-{photo.cpl_num}
        </p>
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-sm font-medium mb-1">
              New accession number
            </label>
            <input
              type="text"
              value={accession}
              onChange={(e) => setAccession(e.target.value)}
              className="w-full px-3 py-3 rounded-md bg-gray-800 border border-gray-600 text-sm text-white focus:outline-none focus:ring-2 focus:ring-green-500"
              placeholder="ie: 1234"
              autoFocus
            />
          </div>
          <div>
            <label className="block text-sm font-medium mb-1">
              New accession year
            </label>
            <YearSelect
              className="w-full px-3 py-3 rounded-md bg-gray-800 border border-gray-600 text-sm text-white focus:outline-none focus:ring-2 focus:ring-green-500"
              showAllOption={false}
              targetYear={2015}
              value={Number(year)}
              onChange={(newYear) => {
                if (newYear === null) return;
                setYear(newYear.toString());
              }}
            />
          </div>

          {error && <p className="text-sm text-red-400">{error}</p>}

          <div className="flex justify-end gap-2 mt-4">
            <button
              type="button"
              onClick={onCancel}
              disabled={loading}
              className="px-4 py-2 rounded-md text-sm bg-gray-700 hover:bg-gray-600 disabled:opacity-60"
            >
              Cancel
            </button>
            <button
              type="submit"
              disabled={loading}
              className="px-4 py-2 rounded-md text-sm bg-green-600 hover:bg-green-500 disabled:opacity-60"
            >
              {loading ? "Saving..." : "Save"}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
