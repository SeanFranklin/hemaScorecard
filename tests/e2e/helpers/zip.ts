import zlib from 'zlib';

/**
 * Minimal reader for the zips the app builds with PHP's ZipArchive: maps
 * each entry name to its text content. Walks the central directory, so it
 * handles stored and deflated entries but not zip64 or encryption.
 */
export function readZip(buffer: Buffer): Map<string, string> {
  // End of central directory record (PK\x05\x06) holds the entry count and
  // where the central directory starts.
  const end = buffer.lastIndexOf(Buffer.from([0x50, 0x4b, 0x05, 0x06]));
  if (end < 0) {
    throw new Error('Not a zip file');
  }
  const entryCount = buffer.readUInt16LE(end + 10);
  let offset = buffer.readUInt32LE(end + 16);

  const files = new Map<string, string>();
  for (let i = 0; i < entryCount; i++) {
    // Central directory file header (PK\x01\x02).
    const method = buffer.readUInt16LE(offset + 10);
    const compressedSize = buffer.readUInt32LE(offset + 20);
    const nameLength = buffer.readUInt16LE(offset + 28);
    const extraLength = buffer.readUInt16LE(offset + 30);
    const commentLength = buffer.readUInt16LE(offset + 32);
    const localHeader = buffer.readUInt32LE(offset + 42);
    const name = buffer.toString('utf8', offset + 46, offset + 46 + nameLength);

    // The local header repeats the name and has its own extra field.
    const dataStart = localHeader + 30
      + buffer.readUInt16LE(localHeader + 26)
      + buffer.readUInt16LE(localHeader + 28);
    const data = buffer.subarray(dataStart, dataStart + compressedSize);
    files.set(name, (method === 8 ? zlib.inflateRawSync(data) : data).toString('utf8'));

    offset += 46 + nameLength + extraLength + commentLength;
  }
  return files;
}
