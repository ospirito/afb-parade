import { registerPlugin } from "@wordpress/plugins";
import { PluginSidebar } from "@wordpress/edit-post";
import { PanelBody, TextControl, Button, Spinner, Notice } from "@wordpress/components";
import { useSelect } from "@wordpress/data";
import { useState, useEffect } from "@wordpress/element";
import apiFetch from "@wordpress/api-fetch";

const ShortlinkSidebar = () => {
	const currentPostId = useSelect((select) => select("core/editor").getCurrentPostId(), []);
	
	const [shortlinks, setShortlinks] = useState([]);
	const [isLoading, setIsLoading] = useState(true);
	const [notice, setNotice] = useState(null);
	const [slug, setSlug] = useState("");
	const defaultParams = window.AFBShortlinkData?.defaultParams || [{ key: "", value: "" }];
	const [queryParams, setQueryParams] = useState(defaultParams);
	const [editingId, setEditingId] = useState(null);
	const [copiedId, setCopiedId] = useState(null);
	
	const fetchShortlinks = async () => {
		setIsLoading(true);
		try {
			const data = await apiFetch({ path: `/afb-parade/v1/shortlinks/${currentPostId}` });
			setShortlinks(data);
		} catch (err) {
			console.error(err);
			setNotice({ status: "error", message: "Failed to load shortlinks." });
		}
		setIsLoading(false);
	};

	useEffect(() => {
		if (currentPostId) {
			fetchShortlinks();
		}
	}, [currentPostId]);

	const createShortlink = async () => {
		if (!currentPostId) return;
		setIsLoading(true);
		setNotice(null);
		
		const queryString = queryParams
			.filter((p) => p.key.trim() !== '' && p.value.trim() !== '')
			.map((p) => `${encodeURIComponent(p.key.trim())}=${encodeURIComponent(p.value.trim())}`)
			.join('&');

		try {
			if (editingId) {
				await apiFetch({
					path: `/afb-parade/v1/shortlinks/update/${editingId}`,
					method: "POST",
					data: { slug, query_params: queryString },
				});
				setNotice({ status: "success", message: "Shortlink updated!" });
			} else {
				await apiFetch({
					path: `/afb-parade/v1/shortlinks/${currentPostId}`,
					method: "POST",
					data: { slug, query_params: queryString },
				});
				setNotice({ status: "success", message: "Shortlink created!" });
			}
			setSlug("");
			setQueryParams(defaultParams);
			setEditingId(null);
			fetchShortlinks();
		} catch (err) {
			setNotice({ status: "error", message: err.message || "Error saving shortlink." });
			setIsLoading(false);
		}
	};

	const copyToClipboard = (linkId, text) => {
		const handleSuccess = () => {
			setCopiedId(linkId);
			setTimeout(() => setCopiedId(null), 2000);
			setNotice({ status: "success", message: "Link copied to clipboard!" });
			setTimeout(() => { if (setNotice) setNotice(null); }, 3000);
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

	const startEditing = (link) => {
		setEditingId(link.id);
		setSlug(link.slug);
		
		if (link.query_params) {
			const pairs = link.query_params.split('&').map(pair => {
				const [key, value] = pair.split('=').map(decodeURIComponent);
				return { key, value };
			});
			setQueryParams(pairs);
		} else {
			setQueryParams([{ key: "", value: "" }]);
		}
	};

	const cancelEditing = () => {
		setEditingId(null);
		setSlug("");
		setQueryParams(defaultParams);
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

	return (
		<PluginSidebar name="afb-parade-shortlinks" title="URL Shortener" icon="admin-links">
			<PanelBody title="Manage Shortlinks">
				{notice && (
					<Notice status={notice.status} onRemove={() => setNotice(null)}>
						{notice.message}
					</Notice>
				)}

				<div style={{ marginBottom: "20px" }}>
					<TextControl
						label="Custom Slug (leave blank for random)"
						value={slug}
						onChange={(val) => setSlug(val)}
						help="e.g. 'summer-event'"
						__nextHasNoMarginBottom
					/>
					
					<div style={{ marginTop: "15px", marginBottom: "15px" }}>
						<strong>Query Parameters</strong>
						{queryParams.map((param, index) => (
							<div key={index} style={{ display: 'flex', gap: '5px', marginTop: '10px', alignItems: 'center' }}>
								<TextControl
									placeholder="Key"
									value={param.key}
									onChange={(val) => {
										const newParams = [...queryParams];
										newParams[index].key = val;
										setQueryParams(newParams);
									}}
									__nextHasNoMarginBottom
								/>
								<TextControl
									placeholder="Value"
									value={param.value}
									onChange={(val) => {
										const newParams = [...queryParams];
										newParams[index].value = val;
										setQueryParams(newParams);
									}}
									__nextHasNoMarginBottom
								/>
								<Button 
									variant="link"
									isDestructive 
									size="small" 
									style={{ marginBottom: '8px' }}
									onClick={() => {
										const newParams = queryParams.filter((_, i) => i !== index);
										setQueryParams(newParams);
									}}
								>
									&times;
								</Button>
							</div>
						))}
						<Button variant="secondary" size="small" onClick={() => setQueryParams([...queryParams, { key: "", value: "" }])}>
							+ Add Parameter
						</Button>
					</div>

					<div style={{ display: 'flex', gap: '10px' }}>
						<Button variant="primary" onClick={createShortlink} disabled={isLoading}>
							{editingId ? "Update Shortlink" : "Create Shortlink"}
						</Button>
						{editingId && (
							<Button variant="secondary" onClick={cancelEditing}>
								Cancel
							</Button>
						)}
					</div>
				</div>

				<hr />

				{isLoading && !editingId ? (
					<Spinner />
				) : (
					<ul style={{ padding: 0, margin: 0, listStyle: "none" }}>
						{shortlinks.map((link) => (
							<li key={link.id} style={{ marginBottom: "15px", padding: "10px", border: "1px solid #ddd", borderRadius: "4px", backgroundColor: editingId === link.id ? "#f0f0f0" : "transparent" }}>
								<div 
									onClick={() => copyToClipboard(link.id, `${window.AFBShortlinkData?.baseDomain || "/s/"}${link.slug}`)}
									style={{ cursor: "pointer", display: "flex", justifyContent: "space-between", alignItems: "center" }}
									title="Click to copy shortlink"
								>
									<strong>{window.AFBShortlinkData?.baseDomain || "/s/"}{link.slug}</strong>
									{copiedId === link.id && (
										<span style={{ fontSize: "10px", color: "#46b450", fontWeight: "bold", textTransform: "uppercase" }}>Copied!</span>
									)}
								</div>
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
								<div style={{ display: 'flex', gap: '10px', marginTop: '10px' }}>
									<Button variant="secondary" size="small" onClick={() => startEditing(link)}>
										Edit
									</Button>
									<Button variant="primary" isDestructive size="small" onClick={() => deleteShortlink(link.id)}>
										Delete
									</Button>
								</div>
							</li>
						))}
						{shortlinks.length === 0 && <p>No shortlinks yet.</p>}
					</ul>
				)}
			</PanelBody>
		</PluginSidebar>
	);
};

registerPlugin("afb-parade-shortlinks", {
	render: ShortlinkSidebar,
});
