import { useState } from "react";
import ImageUploading, { type ImageListType } from "react-images-uploading";
import { uploadPhoto } from "../services/photoService";

export default function UploadPhoto() {
  const [images, setImages] = useState<ImageListType>([]);
  const [uploading, setUploading] = useState(false);
  const [uploadMessage, setUploadMessage] = useState<string>("");
  const [formData, setFormData] = useState({
    cpl_num: "",
    year: new Date().getFullYear().toString(),
  });
  const maxNumber = 40;

  const onChange = (imageList: ImageListType, addUpdateIndex?: number[]) => {
    console.log(imageList, addUpdateIndex);
    setImages(imageList);
    // Clear any previous upload messages when images change
    setUploadMessage("");
  };

  const handleInputChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>,
  ) => {
    const { name, value } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: value,
    }));
  };

  const handleUpload = async () => {
    if (images.length === 0) {
      setUploadMessage("Please select at least one image to upload.");
      return;
    }

    if (!formData.cpl_num.trim() || !formData.year.trim()) {
      setUploadMessage("Please fill in CPL Number and Year.");
      return;
    }

    setUploading(true);
    setUploadMessage("");

    try {
      // Upload each image with metadata
      const uploadPromises = images.map(async (image) => {
        if (!image.file) {
          throw new Error("No file data available for upload");
        }

        const uploadFormData = new FormData();
        uploadFormData.append("photo", image.file);
        uploadFormData.append("cpl_num", formData.cpl_num);
        uploadFormData.append("year", formData.year);

        return uploadPhoto(uploadFormData);
      });

      const results = await Promise.all(uploadPromises);

      // Check if all uploads were successful
      const allSuccessful = results.every((result) => result.success);

      if (allSuccessful) {
        setUploadMessage(`Successfully uploaded ${results.length} image(s)!`);
        // Clear images and reset form after successful upload
        setImages([]);
        setFormData({
          cpl_num: "",
          year: new Date().getFullYear().toString(),
        });
      } else {
        const failedCount = results.filter((result) => !result.success).length;
        setUploadMessage(
          `Failed to upload ${failedCount} image(s). Please try again.`,
        );
      }
    } catch (error) {
      console.error("Upload error:", error);
      setUploadMessage("An error occurred during upload. Please try again.");
    } finally {
      setUploading(false);
    }
  };

  return (
    <div className="m-6">
      <h3 className="text-3xl font-bold mb-6 text-gray-100">Upload Images</h3>

      {uploadMessage && (
        <div
          className={`mb-4 p-3 rounded-md ${
            uploadMessage.includes("Successfully")
              ? "bg-green-100 text-green-800 border border-green-200"
              : "bg-red-100 text-red-800 border border-red-200"
          }`}
        >
          {uploadMessage}
        </div>
      )}

      {/* Metadata Form */}
      <div className="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200 max-w-md">
        <h4 className="text-xl font-semibold mb-4 text-gray-800">
          Photo Information
        </h4>
        <div className="flex flex-col gap-3">
          <div>
            <label
              htmlFor="cpl_num"
              className="block text-base font-semibold text-gray-800 mb-2"
            >
              Accession Number:
            </label>
            <input
              type="text"
              id="cpl_num"
              name="cpl_num"
              value={formData.cpl_num}
              onChange={handleInputChange}
              placeholder="e.g. 0047"
              className="w-full max-w-[180px] md:max-w-[200px] px-3 py-2 text-base border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              disabled={uploading}
              required
            />
          </div>

          <div>
            <label
              htmlFor="year"
              className="block text-base font-semibold text-gray-800 mb-2"
            >
              Accession Year (4-digit):
            </label>
            <input
              type="number"
              id="year"
              name="year"
              value={formData.year}
              onChange={handleInputChange}
              placeholder="2024"
              min="1900"
              max="2100"
              className="w-full max-w-[180px] md:max-w-[200px] px-3 py-2 text-base border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              disabled={uploading}
              required
            />
          </div>
        </div>
        <p className="text-base text-gray-600 mt-3">
          Suffix will be auto-determined based on existing accession number.
        </p>
        <div className="text-lg text-gray-700 mt-2">
          Images(s) will be uploaded to accession{" "}
          <span className="font-semibold">
            {formData.year.slice(-2)}x-
            {formData.cpl_num.padStart(4, "0")}
          </span>
        </div>
      </div>

      <ImageUploading
        multiple
        value={images}
        onChange={onChange}
        maxNumber={maxNumber}
        dataURLKey="img_url"
        acceptType={["jpg", "jpeg", "png"]}
      >
        {({
          imageList,
          onImageUpload,
          onImageRemoveAll,
          onImageUpdate,
          onImageRemove,
          isDragging,
          dragProps,
        }) => (
          // write your building UI
          <div className="image-wrapper">
            <div className="flex gap-4 mb-4">
              <button
                style={isDragging ? { color: "red" } : undefined}
                onClick={onImageUpload}
                disabled={uploading}
                className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed"
                {...dragProps}
              >
                {isDragging ? "Drop images here" : "Select Images"}
              </button>

              {images.length > 0 && (
                <button
                  onClick={handleUpload}
                  disabled={
                    uploading ||
                    !formData.cpl_num.trim() ||
                    !formData.year.trim()
                  }
                  className="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed"
                >
                  {uploading
                    ? "Uploading..."
                    : `Upload ${images.length} Image${images.length > 1 ? "s" : ""}`}
                </button>
              )}
            </div>

            {imageList.map((image, index) => (
              <div
                key={index}
                className="image-item mb-4 p-4 border border-gray-200 rounded-lg"
              >
                <img
                  src={image["img_url"]}
                  alt=""
                  className="mb-2 rounded-lg max-w-full h-auto"
                  style={{ maxWidth: "600px" }}
                />

                <div className="image-item__btn-wrapper flex gap-2">
                  <button
                    onClick={() => onImageUpdate(index)}
                    disabled={uploading}
                    className="px-3 py-1 bg-yellow-600 text-white rounded hover:bg-yellow-700 disabled:bg-gray-400"
                  >
                    Replace
                  </button>
                  <button
                    onClick={() => onImageRemove(index)}
                    disabled={uploading}
                    className="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 disabled:bg-gray-400"
                  >
                    Remove
                  </button>
                </div>
              </div>
            ))}

            {images.length > 1 && (
              <button
                onClick={onImageRemoveAll}
                disabled={uploading}
                className="mt-4 px-4 py-2 bg-red-800 text-white rounded-md hover:bg-red-900 disabled:bg-gray-400 disabled:cursor-not-allowed"
              >
                Remove all images
              </button>
            )}
          </div>
        )}
      </ImageUploading>
    </div>
  );
}
