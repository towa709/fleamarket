let isEditing = false;

document.addEventListener("DOMContentLoaded", async () => {
  await loadSidebarTransactions();

  const res = await fetch(`/api/chat/partner/${window.TRANSACTION_ID}`);
  const data = await res.json();

  document.getElementById("partner-name").textContent = data.name ?? "（名前取得エラー）";
  if (data.image) {
    document.getElementById("partner-image").src = data.image;
  }

  if (window.PRODUCT) {
    const imgPath = window.PRODUCT.image;

    if (imgPath.startsWith("http")) {
      document.getElementById("product-image").src = imgPath;
    } else {
      document.getElementById("product-image").src = `/storage/${imgPath}`;
    }

    document.getElementById("product-name").textContent = window.PRODUCT.name;
    document.getElementById("product-price").textContent =
      `¥${window.PRODUCT.price.toLocaleString()}`;
  }

  const savedDraft = localStorage.getItem(`chat_draft_${window.TRANSACTION_ID}`);
  if (savedDraft) {
    document.getElementById("message-input").value = savedDraft;
  }

  await loadMessages();

  const shouldEvaluate = window.SHOULD_EVALUATE ?? false;
  const modal = document.getElementById("evaluation-modal");
  const finishBtn = document.getElementById("finish-transaction");
  const stars = document.querySelectorAll(".star-select span");

  let selectedScore = 0;

  if (shouldEvaluate && modal && !finishBtn) {
    modal.classList.remove("hide");
  }

  if (finishBtn) {
    finishBtn.addEventListener("click", () => {
      if (modal) modal.classList.remove("hide");
    });
  }

  stars.forEach(star => {
    star.addEventListener("click", () => {
      selectedScore = parseInt(star.dataset.score);
      stars.forEach(s => {
        s.classList.remove("selected");
        if (parseInt(s.dataset.score) <= selectedScore) {
          s.classList.add("selected");
        }
      });
    });
  });

  document.addEventListener("click", async (e) => {
    if (e.target && e.target.id === "send-evaluation") {
      if (selectedScore === 0) {
        alert("星を選んでください。");
        return;
      }

      const res = await fetch(`/evaluation/${window.TRANSACTION_ID}`, {
        method: "POST",
        headers: {
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ score: selectedScore })
      });

      const data = await res.json();

      if (data.status === "ok") {
        modal.classList.add("hide");
        window.location.href = "/";
      }
    }
  });
});

async function loadSidebarTransactions() {
  const res = await fetch('/api/chat/list');
  const list = await res.json();

  const ul = document.getElementById('transaction-list');
  ul.innerHTML = '';

  list.forEach(t => {
    if (t.transaction_id == window.TRANSACTION_ID) return;

    const li = document.createElement('li');
    li.classList.add('sidebar-item');

    if (t.transaction_id == window.TRANSACTION_ID) {
      li.classList.add('active');
    }

    li.innerHTML = `
      <a href="/chat/${t.transaction_id}" class="sidebar-link">
        <div class="sidebar-text">
          <div class="sidebar-name">${t.item_name}</div>
        </div>
        ${t.unread_count > 0 ? `<span class="sidebar-unread">${t.unread_count}</span>` : ''}
      </a>
    `;
    ul.appendChild(li);
  });
}

async function loadMessages() {
  const res = await fetch(`/api/chat/${window.TRANSACTION_ID}`);
  const messages = await res.json();

  const area = document.getElementById("message-area");

  const isAtBottom =
    area.scrollHeight - area.scrollTop <= area.clientHeight + 50;

  area.innerHTML = "";

  messages.forEach(msg => {
    const div = document.createElement("div");
    const isMine = msg.user_id === window.AUTH_USER_ID;

    if (!isMine) {
      div.classList.add("message-left");
      div.innerHTML = `
        <div class="top-row">
          <div class="icon">
            <img src="${msg.user_image}" class="icon-img">
          </div>
          <div class="username">${msg.user_name}</div>
        </div>
        <div class="bubble">${msg.message ?? ''}</div>
        ${msg.image ? `<img src="${msg.image}" class="chat-image">` : ''}
      `;
    } else {
      div.classList.add("message-right");
      div.setAttribute("data-id", msg.id);

      div.innerHTML = `
        <div class="top-row">
          <div class="username">${window.AUTH_USER_NAME}</div>
          <div class="icon">
            <img src="${window.AUTH_USER_IMAGE}" class="icon-img">
          </div>
        </div>

        <div class="bubble" data-bubble="true">
          ${msg.message ?? ''}
        </div>

        ${msg.image ? `<img src="${msg.image}" class="chat-image">` : ''}

        <div class="msg-actions">
          <button class="msg-edit">編集</button>
          <button class="msg-delete">削除</button>
        </div>
      `;
    }

    area.appendChild(div);
  });

  if (isAtBottom) {
    area.scrollTop = area.scrollHeight;
  }

  fetch(`/api/chat/${window.TRANSACTION_ID}/read`, {
    method: "POST",
    headers: {
      "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
      "Accept": "application/json",
    },
  });
}

document.getElementById("chat-form").addEventListener("submit", async (e) => {
  e.preventDefault();

  const form = document.getElementById("chat-form");
  const fd = new FormData(form);

  const res = await fetch(`/api/chat/${window.TRANSACTION_ID}`, {
    method: "POST",
    headers: {
      "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
      "Accept": "application/json",
    },
    body: fd,
  });

  if (res.status === 422) {
    const data = await res.json();
    showErrors(data.errors);
    return;
  }

  form.reset();
  document.getElementById("image-input").value = "";
  document.getElementById("image-preview").style.display = "none";
  clearErrors();

  localStorage.removeItem(`chat_draft_${window.TRANSACTION_ID}`);

  await loadMessages();
});

function showErrors(errors) {
  const box = document.getElementById("chat-error-box");
  box.innerHTML = "";

  Object.values(errors).forEach((msgArr) => {
    msgArr.forEach((msg) => {
      box.innerHTML += `<div class="chat-error">${msg}</div>`;
    });
  });
}

function clearErrors() {
  document.getElementById("chat-error-box").innerHTML = "";
}

document.getElementById("image-input").addEventListener("change", function () {
  const file = this.files[0];

  const previewBox = document.getElementById("image-preview");
  const previewImg = document.getElementById("preview-img");

  if (!file) {
    previewBox.style.display = "none";
    previewImg.src = "";
    return;
  }

  const reader = new FileReader();
  reader.onload = function (e) {
    previewImg.src = e.target.result;
    previewBox.style.display = "block";
  };
  reader.readAsDataURL(file);
});

document.getElementById("message-input").addEventListener("input", () => {
  const value = document.getElementById("message-input").value;
  localStorage.setItem(`chat_draft_${window.TRANSACTION_ID}`, value);
});

document.addEventListener("click", (e) => {
  if (e.target.classList.contains("msg-edit")) {
    const msgDiv = e.target.closest(".message-right");
    const msgId = msgDiv.dataset.id;
    const bubble = msgDiv.querySelector("[data-bubble]");

    if (msgDiv.querySelector(".msg-edit-area")) return;

    const originalText = bubble.innerText;
    isEditing = true;

    const actions = msgDiv.querySelector(".msg-actions");
    if (actions) actions.style.display = "none";

    bubble.outerHTML = `
      <textarea class="msg-edit-area" data-edit-id="${msgId}">${originalText}</textarea>
      <div class="msg-edit-buttons">
        <button class="msg-save edit-btn-save">保存</button>
        <button class="msg-cancel edit-btn-cancel">キャンセル</button>
      </div>
    `;
  }
});

document.addEventListener("click", async (e) => {
  if (e.target.classList.contains("msg-save")) {
    const msgDiv = e.target.closest(".message-right");
    const textarea = msgDiv.querySelector(".msg-edit-area");
    const msgId = textarea.dataset.editId;
    const newText = textarea.value;

    const res = await fetch(`/api/chat/message/${msgId}`, {
      method: "PATCH",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify({ message: newText }),
    });

    if (res.ok) {
      isEditing = false;
      await loadMessages();
    }
  }
});

document.addEventListener("click", (e) => {
  if (e.target.classList.contains("msg-cancel")) {
    isEditing = false;
    loadMessages();
  }
});

let deleteTargetId = null;

document.addEventListener("click", (e) => {
  if (e.target.classList.contains("msg-delete")) {
    const msgDiv = e.target.closest(".message-right");
    deleteTargetId = msgDiv.dataset.id;

    document.getElementById("delete-modal").classList.remove("hide");
  }
});

document.getElementById("delete-cancel").addEventListener("click", () => {
  deleteTargetId = null;
  document.getElementById("delete-modal").classList.add("hide");
});

document.getElementById("delete-confirm").addEventListener("click", async () => {
  if (!deleteTargetId) return;

  const res = await fetch(`/api/chat/message/${deleteTargetId}`, {
    method: "DELETE",
    headers: {
      "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
    },
  });

  if (res.ok) {
    await loadMessages();
  }

  deleteTargetId = null;
  document.getElementById("delete-modal").classList.add("hide");
});

setInterval(() => {
  if (!isEditing) {
    loadMessages();
    loadSidebarTransactions();
  }
}, 2000);
