import { render, useState, useEffect } from "@wordpress/element";
import { Button, Spinner, Notice, TextControl } from "@wordpress/components";
import apiFetch from "@wordpress/api-fetch";

const ShortlinkManager = () => {
	const [shortlinks, setShortlinks] = useState([]);
	const [isLoading, setIsLoading] = useState(true);
	const [notice, setNotice] = useState(null);
	const [editingId, setEditingId] = useState(null);
	const [copiedId, setCopiedId] = useState(null);
	const [activeQrLinkId, setActiveQrLinkId] = useState(null);

	// Create form state
	const [isCreating, setIsCreating] = useState(false);
	const [createTargetUrl, setCreateTargetUrl] = useState("");
	const [createSlug, setCreateSlug] = useState("");
	const defaultParams = window.AFBShortlinkData?.defaultParams || [{ key: "", value: "" }];
	const [createQueryParams, setCreateQueryParams] = useState(defaultParams);

	// Edit form state
	const [editTargetUrl, setEditTargetUrl] = useState("");
	const [editSlug, setEditSlug] = useState("");
	const [editQueryParams, setEditQueryParams] = useState(defaultParams);

	const baseDomain = window.AFBShortlinkData?.baseDomain || "/s/";

	const downloadQrCode = (qrUrl, slug) => {
		fetch(qrUrl)
			.then((res) => res.blob())
			.then((blob) => {
				const blobUrl = URL.createObjectURL(blob);
				const a = document.createElement("a");
				a.href = blobUrl;
				a.download = `${slug || "shortlink"}-qrcode.png`;
				document.body.appendChild(a);
				a.click();
				document.body.removeChild(a);
				URL.revokeObjectURL(blobUrl);
			})
			.catch(() => {
				window.open(qrUrl, "_blank", "noopener,noreferrer");
			});
	};

	const fetchShortlinks = async () => {
		setIsLoading(true);
		try {
			const data = await apiFetch({ path: "/afb-parade/v1/shortlinks" });
			setShortlinks(data);
		} catch (err) {
			console.error(err);
			setNotice({ status: "error", message: "Failed to load shortlinks." });
		}
		setIsLoading(false);
	};

	useEffect(() => {
		fetchShortlinks();
	}, []);

	const createShortlink = async () => {
		if (!createTargetUrl.trim()) {
			setNotice({ status: "error", message: "Please enter a target URL." });
			return;
		}

		setIsLoading(true);
		setNotice(null);

		const queryString = createQueryParams
			.filter((p) => p.key.trim() !== '' && p.value.trim() !== '')
			.map((p) => `${encodeURIComponent(p.key.trim())}=${encodeURIComponent(p.value.trim())}`)
			.join('&');

		try {
			await apiFetch({
				path: "/afb-parade/v1/shortlinks",
				method: "POST",
				data: {
					target_url: createTargetUrl,
					slug: createSlug,
					query_params: queryString,
				},
			});
			setNotice({ status: "success", message: "Shortlink created!" });
			setIsCreating(false);
			setCreateTargetUrl("");
			setCreateSlug("");
			setCreateQueryParams(defaultParams);
			fetchShortlinks();
		} catch (err) {
			setNotice({ status: "error", message: err.message || "Error creating shortlink." });
			setIsLoading(false);
		}
	};

	const saveShortlink = async () => {
		setIsLoading(true);
		setNotice(null);

		const queryString = editQueryParams
			.filter((p) => p.key.trim() !== '' && p.value.trim() !== '')
			.map((p) => `${encodeURIComponent(p.key.trim())}=${encodeURIComponent(p.value.trim())}`)
			.join('&');

		try {
			await apiFetch({
				path: `/afb-parade/v1/shortlinks/update/${editingId}`,
				method: "POST",
				data: {
					target_url: editTargetUrl,
					slug: editSlug,
					query_params: queryString,
				},
			});
			setNotice({ status: "success", message: "Shortlink updated!" });
			setEditingId(null);
			setEditTargetUrl("");
			setEditSlug("");
			setEditQueryParams(defaultParams);
			fetchShortlinks();
		} catch (err) {
			setNotice({ status: "error", message: err.message || "Error updating shortlink." });
			setIsLoading(false);
		}
	};

	const deleteShortlink = async (id) => {
		if (!confirm("Are you sure you want to delete this shortlink?")) return;
		setIsLoading(true);
		try {
			await apiFetch({
				path: `/afb-parade/v1/shortlinks?id=${id}`,
				method: "DELETE",
			});
			setNotice({ status: "success", message: "Shortlink deleted." });
			fetchShortlinks();
		} catch (err) {
			setNotice({ status: "error", message: err.message || "Error deleting." });
			setIsLoading(false);
		}
	};

	const startEditing = (link) => {
		setIsCreating(false);
		setEditingId(link.id);
		setEditSlug(link.slug);
		setEditTargetUrl(link.target_url || "");
		if (link.query_params) {
			const pairs = link.query_params.split("&").map((pair) => {
				const [key, value] = pair.split("=").map(decodeURIComponent);
				return { key, value };
			});
			setEditQueryParams(pairs);
		} else {
			setEditQueryParams([{ key: "", value: "" }]);
		}
	};

	const copyToClipboard = (linkId, text) => {
		const handleSuccess = () => {
			setCopiedId(linkId);
			setTimeout(() => { if (setCopiedId) setCopiedId(null); }, 2000);
			setNotice({ status: "success", message: "Link copied to clipboard!" });
			setTimeout(() => { if (setNotice) setNotice(null); }, 2000);
		};

		if (navigator.clipboard && navigator.clipboard.writeText) {
			navigator.clipboard.writeText(text).then(handleSuccess).catch(() => {
				fallbackCopy(text, handleSuccess);
			});
		} else {
			fallbackCopy(text, handleSuccess);
		}
	};

	const fallbackCopy = (text, callback) => {
		const textArea = document.createElement("textarea");
		textArea.value = text;
		textArea.style.position = "fixed";
		textArea.style.left = "-9999px";
		textArea.style.top = "0";
		document.body.appendChild(textArea);
		textArea.focus();
		textArea.select();
		try {
			const successful = document.execCommand('copy');
			if (successful && callback) callback();
		} catch (err) {
			console.error('Fallback copy failed', err);
		}
		document.body.removeChild(textArea);
	};

	return (
		<div style={{ marginTop: "20px" }}>
			{notice && (
				<Notice status={notice.status} onRemove={() => setNotice(null)}>
					{notice.message}
				</Notice>
			)}

			<div style={{ marginBottom: "20px" }}>
				{!isCreating && !editingId && (
					<Button variant="primary" onClick={() => setIsCreating(true)}>
						+ Create Shortlink to Arbitrary URL
					</Button>
				)}
			</div>

			{isCreating && (
				<div style={{ padding: "20px", border: "1px solid #c3c4c7", borderRadius: "4px", marginBottom: "20px", background: "#fff", boxShadow: "0 1px 3px rgba(0,0,0,0.05)" }}>
					<h3 style={{ marginTop: 0 }}>Create Shortlink</h3>
					<TextControl
						label="Target URL"
						value={createTargetUrl}
						onChange={setCreateTargetUrl}
						placeholder="https://example.com/some-page"
						help="Enter any full URL or internal path to shorten."
						__nextHasNoMarginBottom
					/>
					<div style={{ marginTop: "15px" }}>
						<TextControl
							label="Custom Slug (leave blank for random)"
							value={createSlug}
							onChange={setCreateSlug}
							placeholder="e.g. summer-event"
							__nextHasNoMarginBottom
						/>
					</div>
					<div style={{ marginTop: "15px" }}>
						<strong>Query Parameters</strong>
						{createQueryParams.map((param, index) => (
							<div key={index} style={{ display: "flex", gap: "10px", marginTop: "10px" }}>
								<TextControl
									placeholder="Key"
									value={param.key}
									onChange={(val) => {
										const newParams = [...createQueryParams];
										newParams[index].key = val;
										setCreateQueryParams(newParams);
									}}
									__nextHasNoMarginBottom
								/>
								<TextControl
									placeholder="Value"
									value={param.value}
									onChange={(val) => {
										const newParams = [...createQueryParams];
										newParams[index].value = val;
										setCreateQueryParams(newParams);
									}}
									__nextHasNoMarginBottom
								/>
								<Button variant="link" isDestructive onClick={() => setCreateQueryParams(createQueryParams.filter((_, i) => i !== index))}>&times;</Button>
							</div>
						))}
						<Button variant="secondary" size="small" style={{ marginTop: "10px" }} onClick={() => setCreateQueryParams([...createQueryParams, { key: "", value: "" }])}>
							+ Add Parameter
						</Button>
					</div>
					<div style={{ marginTop: "20px", display: "flex", gap: "10px" }}>
						<Button variant="primary" onClick={createShortlink} disabled={isLoading}>
							Create Shortlink
						</Button>
						<Button variant="secondary" onClick={() => {
							setIsCreating(false);
							setCreateTargetUrl("");
							setCreateSlug("");
							setCreateQueryParams(defaultParams);
						}}>
							Cancel
						</Button>
					</div>
				</div>
			)}

			{editingId && (
				<div style={{ padding: "20px", border: "1px solid #c3c4c7", borderRadius: "4px", marginBottom: "20px", background: "#fff", boxShadow: "0 1px 3px rgba(0,0,0,0.05)" }}>
					<h3 style={{ marginTop: 0 }}>Edit Shortlink</h3>
					<TextControl
						label="Target URL"
						value={editTargetUrl}
						onChange={setEditTargetUrl}
						placeholder="https://example.com/some-page"
						__nextHasNoMarginBottom
					/>
					<div style={{ marginTop: "15px" }}>
						<TextControl
							label="Slug"
							value={editSlug}
							onChange={setEditSlug}
							__nextHasNoMarginBottom
						/>
					</div>
					<div style={{ marginTop: "15px" }}>
						<strong>Query Parameters</strong>
						{editQueryParams.map((param, index) => (
							<div key={index} style={{ display: "flex", gap: "10px", marginTop: "10px" }}>
								<TextControl
									placeholder="Key"
									value={param.key}
									onChange={(val) => {
										const newParams = [...editQueryParams];
										newParams[index].key = val;
										setEditQueryParams(newParams);
									}}
									__nextHasNoMarginBottom
								/>
								<TextControl
									placeholder="Value"
									value={param.value}
									onChange={(val) => {
										const newParams = [...editQueryParams];
										newParams[index].value = val;
										setEditQueryParams(newParams);
									}}
									__nextHasNoMarginBottom
								/>
								<Button variant="link" isDestructive onClick={() => setEditQueryParams(editQueryParams.filter((_, i) => i !== index))}>&times;</Button>
							</div>
						))}
						<Button variant="secondary" size="small" style={{ marginTop: "10px" }} onClick={() => setEditQueryParams([...editQueryParams, { key: "", value: "" }])}>
							+ Add Parameter
						</Button>
					</div>
					<div style={{ marginTop: "20px", display: "flex", gap: "10px" }}>
						<Button variant="primary" onClick={saveShortlink} disabled={isLoading}>
							Save Changes
						</Button>
						<Button variant="secondary" onClick={() => {
							setEditingId(null);
							setEditTargetUrl("");
							setEditSlug("");
							setEditQueryParams(defaultParams);
						}}>
							Cancel
						</Button>
					</div>
				</div>
			)}

			<table className="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th style={{ width: "25%" }}>Short URL</th>
						<th style={{ width: "35%" }}>Target URL / Page</th>
						<th style={{ width: "10%", textAlign: "center" }}>Hits</th>
						<th style={{ width: "15%" }}>Query Params</th>
						<th style={{ width: "15%" }}>Actions</th>
					</tr>
				</thead>
				<tbody>
					{shortlinks.map((link) => {
						const isQrActive = activeQrLinkId === link.id;
						const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(`${baseDomain}${link.slug}`)}`;

						return (
							<tr key={link.id}>
								<td 
									onClick={() => copyToClipboard(link.id, `${baseDomain}${link.slug}`)}
									style={{ cursor: "pointer" }}
									title="Click to copy shortlink"
								>
									<div style={{ display: "flex", flexDirection: "column" }}>
										<strong>{baseDomain}{link.slug}</strong>
										{link.query_params && (
											<div style={{ fontSize: "11px", color: "#666", marginTop: "8px", borderTop: "1px solid #eee", paddingTop: "8px" }}>
												{link.query_params.split('&').map(pair => {
													const [k, v] = pair.split('=').map(decodeURIComponent);
													return (
														<div key={pair} style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '2px' }}>
															<span style={{ fontWeight: '600', color: '#888' }}>{k}</span>
															<span style={{ color: '#444' }}>{v}</span>
														</div>
													);
												})}
											</div>
										)}
										{copiedId === link.id && (
											<span style={{ fontSize: "10px", color: "#46b450", fontWeight: "bold", textTransform: "uppercase" }}>Copied!</span>
										)}
									</div>
								</td>
								<td>
									<a href={link.target_url} target="_blank" rel="noreferrer" style={{ wordBreak: "break-all" }}>
										{link.target_url || link.target_post_title || "N/A"}
									</a>
								</td>
								<td style={{ textAlign: "center", verticalAlign: "middle" }}>
									<span style={{
										display: "inline-block",
										backgroundColor: "#2271b1",
										color: "#ffffff",
										borderRadius: "12px",
										padding: "2px 10px",
										fontSize: "12px",
										fontWeight: "600",
									}}>
										{link.hits_count || 0}
									</span>
								</td>
								<td>{link.query_params ? `?${link.query_params}` : "-"}</td>
								<td>
									<div style={{ display: "flex", gap: "5px", flexWrap: "wrap", flexDirection: "column" }}>
										<div style={{ display: "flex", gap: "5px" }}>
											<Button variant="secondary" size="small" onClick={(e) => { e.stopPropagation(); startEditing(link); }}>Edit</Button>
											<Button variant="primary" isDestructive size="small" onClick={(e) => { e.stopPropagation(); deleteShortlink(link.id); }}>Delete</Button>
											<Button 
												variant={isQrActive ? "primary" : "secondary"} 
												size="small" 
												icon="grid-view" 
												onClick={(e) => {
													e.stopPropagation();
													setActiveQrLinkId(isQrActive ? null : link.id);
												}}
												title={isQrActive ? "Hide QR Code" : "View QR Code"}
											/>
										</div>
										{isQrActive && (
											<div 
												onClick={(e) => e.stopPropagation()}
												style={{ marginTop: "10px", textAlign: "center", background: "#f9f9f9", padding: "10px", borderRadius: "4px", border: "1px solid #ddd" }}
											>
												<img src={qrUrl} alt="QR Code" style={{ maxWidth: "150px", height: "auto" }} />
												<div style={{ marginTop: "5px", display: "flex", justifyContent: "center", gap: "10px" }}>
													<Button variant="link" size="small" onClick={() => downloadQrCode(qrUrl, link.slug)}>Download QR</Button>
													<Button variant="link" size="small" isDestructive onClick={() => setActiveQrLinkId(null)}>Close</Button>
												</div>
											</div>
										)}
									</div>
								</td>
							</tr>
						);
					})}
					{shortlinks.length === 0 && !isLoading && (
						<tr>
							<td colSpan="5">No shortlinks found.</td>
						</tr>
					)}
					{isLoading && (
						<tr>
							<td colSpan="5"><Spinner /> Loading...</td>
						</tr>
					)}
				</tbody>
			</table>
		</div>
	);
};

document.addEventListener("DOMContentLoaded", () => {
	const root = document.getElementById("afb-shortlink-manager-root");
	if (root) {
		render(<ShortlinkManager />, root);
	}
});

