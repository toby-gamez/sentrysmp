// Cart Processor with RCON Functionality
document.addEventListener("DOMContentLoaded", function () {
  // Add a processor button
  const container = document.querySelector(".container");
  if (container) {
    const processorDiv = document.createElement("div");
    processorDiv.className = "cart-processor";
    processorDiv.innerHTML = `
            <h2 class="main">Cart Summary</h2>
            <div id="cart-summary">
                <div id="price-summary"></div>
                <div id="command-summary"></div>
            </div>
            <div class="button-group">
                <button id="process-cart" class="process-btn">Process Cart</button>
                <button id="execute-commands" class="execute-btn">Execute Commands</button>
            </div>
        `;

    // Insert after the cart-list
    const cartList = document.getElementById("cart-list");
    if (cartList && cartList.nextSibling) {
      container.insertBefore(processorDiv, cartList.nextSibling);
    } else {
      container.appendChild(processorDiv);
    }

    // Add styles
    const style = document.createElement("style");
    style.textContent = `
            .cart-processor {
                margin-top: 30px;
                padding: 20px;
                background-color: var(--bg-secondary);
                border-radius: 10px;
            }
            #cart-summary {
                margin: 15px 0;
                padding: 15px;
                background-color: var(--bg-primary);
                border-radius: 8px;
            }
            #price-summary {
                font-size: 18px;
                margin-bottom: 15px;
            }
            #command-summary {
                font-family: monospace;
                white-space: pre-wrap;
                background-color: var(--bg-tertiary);
                padding: 10px;
                border-radius: 5px;
                max-height: 300px;
                overflow-y: auto;
            }
            .button-group {
                display: flex;
                gap: 10px;
                margin-top: 15px;
            }
            .process-btn, .execute-btn {
                padding: 10px 20px;
                cursor: pointer;
            }
            .process-btn {
                background-color: #4CAF50;
                color: white;
            }
            .execute-btn {
                background-color: #2196F3;
                color: white;
            }
            .command-item {
                padding: 5px;
                border-bottom: 1px solid var(--border-color);
            }
        `;
    document.head.appendChild(style);

    // Initialize functionality
    setupCartProcessor();
  }

  function setupCartProcessor() {
    const processButton = document.getElementById("process-cart");
    const executeButton = document.getElementById("execute-commands");

    if (processButton) {
      processButton.addEventListener("click", processCart);
    }

    if (executeButton) {
      executeButton.addEventListener("click", executeCommands);
    }
  }

  function processCart() {
    const cart = JSON.parse(localStorage.getItem("cart") || "[]");
    if (cart.length === 0) {
      alert("Your cart is empty!");
      return;
    }

    // Initialize variables to store combined results
    let totalPrice = 0;
    const commands = [];
    const username = getUsernameFromStorage();

    // Process spawners first
    processSpawners()
      .then((spawnerResults) => {
        // Add spawner results
        totalPrice += spawnerResults.price;
        commands.push(...spawnerResults.commands);

        // Then process keys
        return processKeys();
      })
      .then((keyResults) => {
        // Add key results
        totalPrice += keyResults.price;
        commands.push(...keyResults.commands);

        // Then process ranks
        return processRanks();
      })
      .then((rankResults) => {
        // Add rank results
        totalPrice += rankResults.price;
        commands.push(...rankResults.items);

        // Display total price
        const priceSummary = document.getElementById("price-summary");
        priceSummary.innerHTML = `<strong>Total Price:</strong> ${totalPrice}€`;

        // Store IDs to fetch commands
        localStorage.setItem("processingCart", JSON.stringify(commands));

        // Fetch the commands
        fetchCommands(commands, username);
      })
      .catch((error) => {
        console.error("Error processing cart:", error);
        alert("Error processing cart. Please try again later.");
      });

    function processSpawners() {
      return fetch("shards.php")
        .then((response) => response.text())
        .then((html) => {
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, "text/html");
          const spawners = doc.querySelectorAll(".spawner");

          let price = 0;
          const cmds = [];

          // Process each spawner in the cart
          spawners.forEach((spawner) => {
            const id = String(spawner.getAttribute("data-id"));
            // Accept both plain id and spawner_ prefix
            const inCart = cart.some(
              (item) =>
                String(item) === id ||
                String(item) === `spawner_${id}` ||
                (typeof item === "object" &&
                  (String(item.id) === id ||
                    String(item.id) === `spawner_${id}`)),
            );
            if (inCart) {
              // Get price from description
              const description = spawner.querySelector("p").textContent;
              const priceMatch = description.match(/(\d+)€/);

              if (priceMatch) {
                price += parseInt(priceMatch[1]);
              }

              // Find the command associated with this spawner
              const spawnerName = spawner.querySelector("h2").textContent;

              cmds.push({
                id: id,
                name: spawnerName,
                type: "spawner",
              });
            }
          });

          return { price: price, commands: cmds };
        });
    }

    function processKeys() {
      return fetch("keys.php")
        .then((response) => response.text())
        .then((html) => {
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, "text/html");
          const keys = doc.querySelectorAll(".spawner");

          let price = 0;
          const cmds = [];

          // Process each key in the cart
          keys.forEach((key) => {
            const id = String(key.getAttribute("data-id"));
            // Accept both plain id and key_ prefix
            const inCart = cart.some(
              (item) =>
                String(item) === id ||
                String(item) === `key_${id}` ||
                (typeof item === "object" &&
                  (String(item.id) === id || String(item.id) === `key_${id}`)),
            );
            if (inCart) {
              // Get price from description
              const description = key.querySelector("p").textContent;
              const priceMatch = description.match(/(\d+)€/);

              if (priceMatch) {
                price += parseInt(priceMatch[1]);
              }

              // Find the command associated with this key
              const keyName = key.querySelector("h2").textContent;

              cmds.push({
                id: id,
                name: keyName,
                type: "key",
              });
            }
          });

          return { price: price, commands: cmds };
        });
    }

    function processRanks() {
      return fetch("ranks.php")
        .then((response) => response.text())
        .then((html) => {
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, "text/html");
          const ranks = doc.querySelectorAll(".spawner");

          let price = 0;
          const cmds = [];

          // Check for new cart format first
          const newCart = JSON.parse(localStorage.getItem("cart") || "[]");
          const hasNewCart =
            Array.isArray(newCart) &&
            newCart.length > 0 &&
            typeof newCart[0] === "object";

          // Process each rank in the cart
          ranks.forEach((rank) => {
            const id = String(rank.getAttribute("data-id"));
            let quantity = 1;
            let inCart = false;

            // Check if this rank is in the cart
            if (hasNewCart) {
              // New cart format (array of objects)
              const cartItem = newCart.find(
                (item) =>
                  String(item.id) === id || String(item.id) === `rank_${id}`,
              );
              if (cartItem) {
                inCart = true;
                quantity = cartItem.quantity || 1;
              }
            } else if (cart.includes(id) || cart.includes(`rank_${id}`)) {
              // Old cart format (array of ids)
              inCart = true;
            }

            if (inCart) {
              // Get price from price container or description
              let rankPrice = 0;
              const priceContainer = rank.querySelector(".price-container");

              if (priceContainer) {
                const discountedPrice =
                  priceContainer.querySelector(".discounted-price");
                if (discountedPrice) {
                  const priceMatch =
                    discountedPrice.textContent.match(/(\d+(?:\.\d+)?)€/);
                  if (priceMatch) {
                    rankPrice = parseFloat(priceMatch[1]);
                  }
                }
              } else {
                const description = rank.querySelector("p").textContent;
                const priceMatch = description.match(/(\d+(?:\.\d+)?)€/);
                if (priceMatch) {
                  rankPrice = parseFloat(priceMatch[1]);
                }
              }

              // Multiply price by quantity
              price += rankPrice * quantity;

              // Find the rank name
              const rankName = rank.querySelector("h2").textContent;

              cmds.push({
                id: id,
                name: rankName,
                type: "rank",
                quantity: quantity,
                price: rankPrice,
              });
            }
          });

          return {
            price,
            items: cmds,
          };
        });
    }

    function fetchCommands(itemInfo, username) {
      // We'll need to fetch commands for all item types
      const spawnerItems = itemInfo.filter(
        (item) =>
          !item.id.toString().startsWith("key_") && item.type !== "rank",
      );
      const keyItems = itemInfo.filter((item) =>
        item.id.toString().startsWith("key_"),
      );
      const rankItems = itemInfo.filter((item) => item.type === "rank");

      const allCommands = [];

      // First fetch spawner commands
      fetchSpawnerCommands()
        .then((spawnerCommands) => {
          // Add spawner commands
          allCommands.push(...spawnerCommands);

          // Then fetch key commands
          return fetchKeyCommands();
        })
        .then((keyCommands) => {
          // Add key commands
          allCommands.push(...keyCommands);

          // Then fetch rank commands
          return fetchRankCommands();
        })
        .then((rankCommands) => {
          // Add rank commands
          allCommands.push(...rankCommands);

          // Display all commands
          showCommands(allCommands);

          // Store commands for execution
          localStorage.setItem(
            "commandsToExecute",
            JSON.stringify(allCommands),
          );
        })
        .catch((error) => {
          console.error("Error fetching commands:", error);
        });
    }

    function fetchSpawnerCommands() {
      return new Promise((resolve, reject) => {
        if (spawnerItems.length === 0) {
          resolve([]);
          return;
        }

        // Create a hidden iframe to load edit_spawners.php content
        const iframe = document.createElement("iframe");
        iframe.style.display = "none";
        iframe.src = "edit_shards.php";

        iframe.onload = function () {
          try {
            const doc = iframe.contentDocument || iframe.contentWindow.document;
            const spawnerForms = doc.querySelectorAll(".spaw form");
            const commands = [];

            spawnerItems.forEach((info) => {
              spawnerForms.forEach((form) => {
                const idInput = form.querySelector('input[name="id"]');
                if (idInput && parseInt(idInput.value) === parseInt(info.id)) {
                  let command = form.querySelector(
                    'input[name="prikaz"]',
                  ).value;

                  // Remove leading slash if present
                  if (command.startsWith("/")) {
                    command = command.substring(1);
                  }

                  // Replace $usernamemc with actual username
                  if (username) {
                    command = command.replace(/\$usernamemc/g, username);
                  }

                  commands.push({
                    name: info.name,
                    command: command,
                    type: "spawner",
                  });
                }
              });
            });

            // Clean up
            document.body.removeChild(iframe);
            resolve(commands);
          } catch (error) {
            console.error("Error fetching spawner commands:", error);
            document.body.removeChild(iframe);
            reject(error);
          }
        };

        document.body.appendChild(iframe);
      });
    }

    function fetchKeyCommands() {
      return new Promise((resolve, reject) => {
        if (keyItems.length === 0) {
          resolve([]);
          return;
        }

        // Create a hidden iframe to load edit_keys.php content
        const iframe = document.createElement("iframe");
        iframe.style.display = "none";
        iframe.src = "edit_keys.php";

        iframe.onload = function () {
          try {
            const doc = iframe.contentDocument || iframe.contentWindow.document;
            const keyForms = doc.querySelectorAll(".edit-key form");
            const commands = [];

            keyItems.forEach((info) => {
              // Extract numeric ID from key ID (remove "key_" prefix)
              const numericId = info.id.toString().replace("key_", "");

              keyForms.forEach((form) => {
                const idInput = form.querySelector('input[name="id"]');
                if (idInput && idInput.value === numericId) {
                  let command = form.querySelector(
                    'input[name="prikaz"]',
                  ).value;

                  // Remove leading slash if present
                  if (command.startsWith("/")) {
                    command = command.substring(1);
                  }

                  // Replace $usernamemc with actual username
                  if (username) {
                    command = command.replace(/\$usernamemc/g, username);
                  }

                  commands.push({
                    name: info.name + " (Key)",
                    command: command,
                    type: "key",
                    keyId: parseInt(numericId),
                  });
                }
              });
            });

            // Clean up
            document.body.removeChild(iframe);
            resolve(commands);
          } catch (error) {
            console.error("Error fetching key commands:", error);
            document.body.removeChild(iframe);
            reject(error);
          }
        };

        document.body.appendChild(iframe);
      });
    }

    function fetchRankCommands() {
      return new Promise((resolve, reject) => {
        if (rankItems.length === 0) {
          resolve([]);
          return;
        }

        // Create a hidden iframe to load edit_ranks.php content
        const iframe = document.createElement("iframe");
        iframe.style.display = "none";
        iframe.src = "edit_ranks.php";

        iframe.onload = function () {
          try {
            const doc = iframe.contentDocument || iframe.contentWindow.document;
            const rankForms = doc.querySelectorAll(".spaw form");
            const commands = [];

            rankItems.forEach((info) => {
              rankForms.forEach((form) => {
                const idInput = form.querySelector('input[name="id"]');
                if (idInput && parseInt(idInput.value) === parseInt(info.id)) {
                  let command = form.querySelector(
                    'input[name="prikaz"]',
                  ).value;

                  // Remove leading slash if present
                  if (command.startsWith("/")) {
                    command = command.substring(1);
                  }

                  // Replace $usernamemc with actual username
                  if (username) {
                    command = command.replace(/\$usernamemc/g, username);
                  }

                  // Calculate quantity
                  const quantity = info.quantity || 1;

                  // Push the command once for each quantity
                  for (let i = 0; i < quantity; i++) {
                    commands.push({
                      name: info.name + " (Rank)",
                      command: command,
                      type: "rank",
                      rankId: parseInt(info.id),
                    });
                  }
                }
              });
            });

            // Clean up
            document.body.removeChild(iframe);
            resolve(commands);
          } catch (error) {
            console.error("Error fetching rank commands:", error);
            document.body.removeChild(iframe);
            reject(error);
          }
        };

        document.body.appendChild(iframe);
      });
    }
  }

  function showCommands(commands) {
    const commandSummary = document.getElementById("command-summary");
    commandSummary.innerHTML = "";

    if (commands.length === 0) {
      commandSummary.textContent = "No commands found.";
      return;
    }

    commands.forEach((cmd) => {
      const cmdDiv = document.createElement("div");
      cmdDiv.className = "command-item";
      cmdDiv.innerHTML = `<strong>${cmd.name}:</strong> <code>${cmd.command}</code>`;
      commandSummary.appendChild(cmdDiv);
    });
  }

  function executeCommands() {
    const commandsStr = localStorage.getItem("commandsToExecute");
    if (!commandsStr) {
      alert("No commands to execute. Please process your cart first.");
      return;
    }

    const commands = JSON.parse(commandsStr);
    const username = getUsernameFromStorage();

    if (!username) {
      alert("Please log in with your Minecraft username first.");
      return;
    }

    // Create a confirmation dialog
    if (
      confirm(
        `Execute ${commands.length} command(s) on the Minecraft server for ${username}?`,
      )
    ) {
      // Show loading state
      const executeBtn = document.getElementById("execute-commands");
      const originalText = executeBtn.textContent;
      executeBtn.textContent = "Executing...";
      executeBtn.disabled = true;

      // Execute commands one by one
      executeCommandSequence(commands, 0, username, executeBtn, originalText);
    }
  }

  function executeCommandSequence(
    commands,
    index,
    username,
    button,
    originalText,
  ) {
    if (index >= commands.length) {
      // All commands executed
      button.textContent = originalText;
      button.disabled = false;
      alert("All commands executed successfully!");
      return;
    }

    const command = commands[index].command;
    const isKeyCommand = commands[index].type === "key";
    const keyId = commands[index].keyId;

    // Prepare request body based on command type
    let requestBody;

    if (isKeyCommand) {
      requestBody = {
        username: username,
        key: true,
        keyId: keyId,
      };
    } else {
      requestBody = {
        username: username,
        command: command,
      };
    }

    // Send RCON command
    fetch("cart-rcon.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(requestBody),
    })
      .then((response) => response.json())
      .then((data) => {
        console.log(
          `Command executed: ${command} (${isKeyCommand ? "key" : "spawner"})`,
        );
        console.log("Response:", data);

        // Update command status in the UI
        const commandItems = document.querySelectorAll(".command-item");
        if (commandItems[index]) {
          commandItems[index].innerHTML +=
            `<span class="status success"> ✓ Executed</span>`;
        }

        // Execute next command with a small delay
        setTimeout(() => {
          executeCommandSequence(
            commands,
            index + 1,
            username,
            button,
            originalText,
          );
        }, 500);
      })
      .catch((error) => {
        console.error("Error executing command:", error);

        // Update command status in the UI
        const commandItems = document.querySelectorAll(".command-item");
        if (commandItems[index]) {
          commandItems[index].innerHTML +=
            `<span class="status error"> ✗ Failed</span>`;
        }

        // Continue with next command
        setTimeout(() => {
          executeCommandSequence(
            commands,
            index + 1,
            username,
            button,
            originalText,
          );
        }, 500);
      });
  }
});

function getUsernameFromStorage() {
  const username = localStorage.getItem("minecraft-username");
  if (username && /^[a-zA-Z0-9_]{3,16}$/.test(username)) {
    return username;
  }
  return null;
}
