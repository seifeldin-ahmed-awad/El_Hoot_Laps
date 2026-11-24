(() => {
  const messages = [
    "Welcome, operator.",
    "VoltGear has holes. Find them.",
    "Break it. Learn why. Then fix it.",
    "Every flag is points on the board."
  ];

  const typedMessage = document.querySelector("#typed-message");
  const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");

  if (!typedMessage || reducedMotion.matches) {
    return;
  }

  let messageIndex = 0;
  let characterIndex = messages[0].length;
  let deleting = false;

  const typeNextCharacter = () => {
    const currentMessage = messages[messageIndex];
    typedMessage.textContent = currentMessage.slice(0, characterIndex);

    if (!deleting && characterIndex === currentMessage.length) {
      deleting = true;
      window.setTimeout(typeNextCharacter, 1500);
      return;
    }

    if (deleting && characterIndex === 0) {
      deleting = false;
      messageIndex = (messageIndex + 1) % messages.length;
    } else {
      characterIndex += deleting ? -1 : 1;
    }

    window.setTimeout(typeNextCharacter, deleting ? 38 : 72);
  };

  window.setTimeout(() => {
    deleting = true;
    typeNextCharacter();
  }, 1500);
})();