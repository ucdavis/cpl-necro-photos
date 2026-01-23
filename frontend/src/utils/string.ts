/**
 * String utility functions
 */

/**
 * Capitalize the first letter of a string
 */
export const capitalize = (str: string): string => {
  return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
};

/**
 * Convert string to title case
 */
export const toTitleCase = (str: string): string => {
  return str
    .split(" ")
    .map((word) => capitalize(word))
    .join(" ");
};
